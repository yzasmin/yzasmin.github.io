Huit questions qu'un recruteur technique peut poser sur le pipeline Vélomagg, avec des réponses tirées de ce qui a réellement été fait.

# Entretien : pipeline streaming Vélomagg

## 1. Pourquoi Redpanda plutôt que Kafka ?

Même protocole client (le code utilise `confluent-kafka`, il fonctionnerait tel quel sur Kafka), mais un
seul binaire, pas de ZooKeeper ni de KRaft à configurer, et un mode `dev-container` qui démarre avec
`--smp 1 --memory 512M --overprovisioned`. Sur un poste de 8 Go avec moins de 1 Go libre, c'était la
condition pour que le reste du compose (PostgreSQL, dbt, Grafana) tienne. En production je garderais
Kafka managé ; ici l'objectif était un `docker compose up -d` qui passe sur une machine modeste.

## 2. Quelle garantie de livraison offre votre consommateur ?

Au moins une fois, rendue idempotente en base. `enable.auto.commit` est à faux : les décalages ne sont
validés qu'après le COMMIT PostgreSQL. Si le processus tombe entre les deux, le lot est relu, et la
contrainte `UNIQUE (station_id, last_reported)` avec `ON CONFLICT DO NOTHING` empêche la double écriture.
Le nombre de doublons écartés est mesuré (`cur.rowcount` comparé à la taille du lot) et enregistré dans
`raw.ingest_batches`, donc c'est un chiffre observé, pas une hypothèse.

## 3. Pourquoi (station_id, last_reported) comme clé de déduplication ?

Parce que le flux publie un instantané complet : les 52 stations partagent la même valeur de
`last_reported`, égale au `last_updated` de l'enveloppe. La clé naturelle d'un relevé est donc le couple
station plus instant de publication. Un identifiant de message ou un horodatage d'insertion ne
dédupliqueraient rien, puisque le producteur peut relire le même instantané.

## 4. Comment mesurez-vous la latence, et que vaut-elle ?

Deux mesures séparées. La latence de bout en bout vaut `ingested_at - last_reported` : elle inclut l'âge
du flux au moment de la lecture, mesuré à 35,9 s en médiane et 60,0 s au maximum, ce qui borne par le bas
ce qu'un pipeline peut promettre sur cette source. La latence propre au pipeline vaut
`ingested_at - fetched_at` : Redpanda plus consommateur plus écriture. `ingested_at` utilise
`clock_timestamp()` et non `now()`, sinon toutes les lignes d'une transaction porteraient l'heure du début
de transaction. Les deux séries sont dans le tableau de bord et dans `sql/resultats.sql`.

## 5. Pourquoi dbt et pas de simples vues SQL ?

Pour trois choses que les vues ne donnent pas : un graphe de dépendances explicite (staging, dimension,
fait, agrégats), des tests déclaratifs versionnés au même endroit que les modèles (unicité, non-nullité,
bornes, relation vers la dimension, fraîcheur de la source), et la matérialisation incrémentale du fait,
qui ne retraite que les `status_id` nouveaux. Le test de bornes est un test générique écrit à la main
plutôt que `dbt_utils`, pour éviter un `dbt deps` au démarrage d'un conteneur.

## 6. Que faites-vous d'un message qui ne respecte pas le schéma ?

Le parsing est une fonction pure (`velomagg.gbfs.parse_status`) qui lève `ValidationError` sur un champ
manquant, un type inattendu, un compteur négatif ou des coordonnées hors bornes. Le message fautif part
dans `raw.rejected_messages` avec son topic, son décalage, sa charge utile et le motif ; le lot continue.
Ces fonctions sont couvertes par 22 tests pytest, dont des cas construits à partir d'une capture réelle
du flux placée dans `tests/fixtures/`.

## 7. Ce pipeline a-t-il tourné, et sur quelles données publiez-vous vos chiffres ?

Oui, mais pas sur mon poste : le moteur Docker y plante au démarrage sur un socket périmé
(`userAnalyticsOtlpHttp.sock`). J'ai donc fait tourner la pile là où Docker existe, dans l'intégration
continue GitHub (run 35797505264, tâche `pipeline`, conclusion `success`) : `docker compose up -d`, rejeu
de l'archive réelle de 5,14 heures collectée le même jour, 8 minutes d'ingestion en direct, `dbt build`,
`pytest`, export SQL des chiffres en artefact. Résultats : 17 264 messages reçus, 16 848 relevés insérés,
52 doublons écartés, 0 message invalide, 42 tests dbt passés, latence de bout en bout 38,44 s en médiane
et 40,99 s au 95e centile sur les relevés en direct. Le débit vient d'un rejeu, je ne le présente pas
comme une mesure de charge ; la latence, elle, vient bien d'une ingestion en direct. Les relevés rejoués
portent `is_replay = true` et sont exclus des calculs de latence, sinon la mesure n'aurait aucun sens.

## 8. Qu'est-ce qui casserait en production, et que feriez-vous d'abord ?

Trois points. La source : un seul flux HTTP, sans authentification ni contrat de service ; une panne
prolongée se verrait dans les tests de fraîcheur dbt mais rien ne ferait de rattrapage automatique.
Le stockage : une table qui grossit de 75 000 lignes par jour, sans partitionnement ni politique de
rétention ; je partitionnerais par mois et j'agrégerais au-delà de quelques semaines. La supervision :
Grafana lit la base sans alertes ni mesure de décalage de consommation exportée. Je commencerais par une
alerte sur la fraîcheur, la plus simple et la plus parlante pour un exploitant.
