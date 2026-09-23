---
slug: data-engineer
titre: 'Vélos en libre-service : pipeline streaming du flux GBFS'
ordre: 4
categorie: 'Pipeline de données'
famille: engineering
resume: 'Les disponibilités Vélomagg de Montpellier publiées toutes les 60 secondes, capturées en continu, dédupliquées et modélisées : Redpanda, PostgreSQL, dbt et Grafana dans un seul docker compose up.'
statut: publie
motif: pipeline
stack: ['Python', 'Redpanda', 'PostgreSQL', 'dbt', 'Docker Compose', 'Grafana']
liens:
  github: 'https://github.com/yzasmin/velomagg-streaming-pipeline'
teaser: 'video/projets/data-engineer.mp4'
metriques:
  - { label: 'Relevés insérés en base', valeur: '16 848' }
  - { label: 'Latence p95 de bout en bout', valeur: '41,0 s' }
  - { label: 'Tests de qualité dbt passés', valeur: '35 / 35' }
  - { label: 'Doublons écartés', valeur: '52' }
---

## Contexte et problème

Le flux GBFS de Montpellier Méditerranée Métropole publie toutes les 60 secondes l'état des 52 stations
Vélomagg : vélos disponibles, bornes libres, station en service ou non. Ce flux ne conserve rien. Dès
qu'un instantané est remplacé, l'information précédente disparaît. L'exploitant qui veut savoir où les
vélos manquent en fin de journée, ou l'usager qui veut comprendre si sa station est fiable le matin, n'ont
aucune donnée historique à consulter.

Ce projet construit la brique manquante : capter ce flux en continu, garantir qu'un même instantané ne
soit jamais compté deux fois, ranger le tout dans un modèle analytique testé, et afficher l'état du
pipeline autant que celui du réseau. Tout doit démarrer avec une seule commande, sur un poste ordinaire.

## Données

Source : `gbfs.theta.fifteen.eu/gbfs/2.2/montpellier/en`, GBFS 2.2, système `velomagg_montpellier`,
opérateur TaM, licence ODbL 1.0 annoncée dans le flux, jeu référencé sur transport.data.gouv.fr.
52 stations, 659 bornes déclarées, `ttl` de 60 secondes.

Deux fichiers sont interrogés : `station_information.json` (nom, coordonnées, capacité) et
`station_status.json` (vélos et bornes disponibles). Une particularité change la conception : les
52 stations partagent la même valeur de `last_reported`, égale au `last_updated` de l'enveloppe.
L'opérateur publie un instantané global, il ne date pas chaque station. La clé de déduplication est donc
le couple (station, instant de publication), et la fraîcheur ne peut pas être évaluée station par station.

La collecte réelle a duré **5,14 heures** le 22 septembre 2026 (13:28 à 18:37 UTC) : 309 interrogations,
308 instantanés distincts, 16 016 relevés de station. L'écart médian entre deux instantanés est de
60 secondes, le maximum de 64 secondes, et aucun intervalle ne dépasse 90 secondes : aucune mise à jour
n'a été manquée. Au moment de la lecture, le flux avait déjà 35,9 secondes d'âge en médiane, 60 au maximum.

## Approche

Le producteur interroge les deux fichiers à la fréquence annoncée par le flux et publie un message par
station dans Redpanda, avec `station_id` en clé pour que les relevés d'une même station restent ordonnés.
Les descriptions de stations ne sont republiées que lorsqu'elles changent (comparaison d'empreinte), sur
un topic compacté.

Le consommateur lit par lots, valide chaque message contre un schéma explicite, écarte les doublons à
l'intérieur du lot, écrit en une transaction PostgreSQL, puis valide les décalages Kafka. Cet ordre est le
cœur du projet : la livraison est « au moins une fois », et c'est la contrainte d'unicité
`(station_id, last_reported)` avec `ON CONFLICT DO NOTHING` qui rend l'écriture idempotente. Chaque lot
enregistre ses compteurs (reçus, valides, insérés, doublons écartés, invalides) dans une table de suivi,
et les messages refusés partent dans `raw.rejected_messages` avec leur motif.

Le schéma est créé par des migrations SQL versionnées, appliquées par un petit lanceur maison qui
enregistre version et somme de contrôle : une migration déjà appliquée puis modifiée fait échouer le
démarrage. dbt prend le relais : vues de staging, dimension station, table de faits incrémentale,
agrégats horaires par station et pour le réseau, classement des stations les plus souvent vides.
Les tests de qualité (unicité, non-nullité, bornes, relation vers la dimension, fraîcheur de la source,
vélos disponibles jamais supérieurs à la capacité) tournent avec les modèles, toutes les 15 minutes.
Grafana est provisionné par fichiers : source de données et tableau de bord sont dans le dépôt.

## Choix techniques

| Choix                                                    | Plutôt que                                        | Pourquoi                                                                                                                                                                    |
| -------------------------------------------------------- | ------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Redpanda en mode dev                                     | Kafka avec KRaft ou ZooKeeper                     | Même protocole client, un seul binaire, démarre avec `--smp 1 --memory 512M` : indispensable sur un poste de 8 Go où le compose doit aussi loger PostgreSQL, dbt et Grafana |
| Clé unique en base plus validation tardive des décalages | Recherche d'un « exactement une fois » applicatif | Le doublon est écarté par la base, le compteur le prouve, et une panne du consommateur n'entraîne aucune perte ni double écriture                                           |
| dbt                                                      | Vues SQL écrites à la main                        | Graphe de dépendances explicite, tests déclaratifs versionnés avec les modèles, table de faits incrémentale qui ne retraite que les nouvelles lignes                        |
| Test générique maison `accepted_range`                   | Paquet `dbt_utils`                                | Évite un `dbt deps` au démarrage du conteneur, donc un accès réseau et un cache de plus, pour trois lignes de Jinja                                                         |
| `clock_timestamp()` pour l'heure d'insertion             | `now()`                                           | `now()` renvoie l'heure de début de transaction : toutes les lignes d'un lot auraient la même heure et la latence mesurée serait fausse                                     |
| Migrations SQL versionnées avec somme de contrôle        | `CREATE TABLE IF NOT EXISTS` au démarrage         | L'historique du schéma est lisible, et une migration modifiée après coup est détectée au lieu d'être silencieusement ignorée                                                |

## Résultats et métriques

Le poste de développement n'a pas de moteur Docker utilisable : Docker Desktop plante au démarrage sur un
socket périmé (`removing stale socket: ... userAnalyticsOtlpHttp.sock: The file cannot be accessed by the
system`), trois tentatives, même erreur. La pile a donc été exécutée en intégration continue GitHub, qui
fournit Docker : [run 35797505264](https://github.com/yzasmin/velomagg-streaming-pipeline/actions/runs/35797505264),
tâche `pipeline`, conclusion `success`. Ce run monte le compose, rejoue l'archive réelle de 5,14 heures,
ingère le flux en direct pendant 8 minutes, exécute `dbt build` et `pytest`, puis exporte les chiffres par
requêtes SQL. Le débit vient donc d'un rejeu d'archive ; seules les 8 minutes en direct mesurent une latence.

| Mesure                                         | Valeur                                                                        | Source                              |
| ---------------------------------------------- | ----------------------------------------------------------------------------- | ----------------------------------- |
| Messages reçus par le consommateur             | 17 264                                                                        | `results/synthese.json`             |
| Relevés insérés en base                        | 16 848                                                                        | `results/synthese.json`             |
| Doublons écartés par la clé unique             | 52                                                                            | `results/synthese.json`             |
| Messages invalides (schéma)                    | 0                                                                             | `results/synthese.json`             |
| Latence de bout en bout en direct (p50 / p95)  | 38,44 s / 40,99 s                                                             | `results/latence.json`              |
| Latence du pipeline seul en direct (p50 / p95) | 2,879 s / 5,431 s                                                             | `results/latence.json`              |
| Âge du flux à la lecture (p50)                 | 34,54 s                                                                       | `results/latence.json`              |
| Tests de qualité dbt                           | 35 réussis sur 35 (le total `PASS=42` de dbt ajoute les 7 modèles construits) | `results/dbt_build.txt`             |
| Tests unitaires Python                         | 22 passés                                                                     | `results/pytest.txt`                |
| Relevés sans aucun vélo                        | 13,59 %                                                                       | `results/vides_pleines_global.json` |
| Relevés sans aucune borne libre                | 0,00 %                                                                        | `results/vides_pleines_global.json` |
| Taux de remplissage moyen                      | 25,31 %                                                                       | `results/vides_pleines_global.json` |
| Relevés avec plus de vélos que la capacité     | 0                                                                             | `results/vides_pleines_global.json` |

La latence dit l'essentiel : sur 38,4 secondes de bout en bout en médiane, 34,5 viennent de l'âge du flux
avant même sa lecture. Tout ce que le pipeline ajoute tient sous 6 secondes au 95e centile, Redpanda,
validation, déduplication et écriture PostgreSQL comprises. Les 52 doublons écartés correspondent
exactement à une interrogation redondante de l'archive, 52 stations rejetées par la clé unique.

![Panneaux du tableau de bord reproduits depuis les données exportées](/images/projets/data-engineer/tableau-de-bord.png)

Lecture métier : sur cette fin d'après-midi de semaine, le réseau ne présente jamais plus de quelques
dizaines de vélos disponibles pour 659 bornes, et 13,59 % des relevés correspondent à une station sans
aucun vélo, alors qu'aucune station n'a jamais été pleine. Sur cette tranche horaire, le problème n'est
pas la saturation des bornes mais la pénurie de vélos, et elle se concentre : quatre stations sur 52 sont
restées vides sur la totalité de la période observée.

![Disponibilité du réseau relevée toutes les 60 secondes](/images/projets/data-engineer/serie-reseau.png)

![Stations les plus souvent vides](/images/projets/data-engineer/stations-vides.png)

## Limites et pistes d'amélioration

Il manque une copie d'écran du tableau de bord Grafana : le runner d'intégration continue n'a pas de
navigateur et le poste pas de moteur Docker, donc personne n'a pu afficher l'interface. Le service
démarre bien (il figure dans `results/docker_compose_ps.txt`), son tableau de bord est provisionné et
commité, et ses quatre panneaux sont reproduits en image à partir des mêmes requêtes SQL. C'est une
preuve que les données existent, pas que Grafana les affiche correctement.

Deuxième limite : le débit observé vient d'un rejeu d'archive, pas d'un flux en direct, et l'ingestion
en direct n'a duré que 8 minutes, soit 468 relevés pour mesurer la latence. Vient ensuite la durée de
collecte : cinq heures d'un mardi après-midi ne disent rien des pointes du matin, du week-end ni de la
météo. Enfin, rien n'est prêt pour la production, ni réplication, ni sauvegarde, ni partitionnement pour
une table qui grossit d'environ 75 000 lignes par jour. La latence de bout en bout restera par ailleurs
bornée par la source elle-même, déjà vieille de 34,5 secondes en médiane à la lecture.
Les suites utiles, dans l'ordre : une alerte sur la fraîcheur plutôt qu'un simple test au prochain
`dbt build`, le partitionnement mensuel de la table de faits, et l'ajout des vélos hors station
(`free_bike_status`), aujourd'hui ignorés.
