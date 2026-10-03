---
slug: radar-entreprises
titre: "Radar économique des entreprises de l'Hérault"
ordre: 7
categorie: 'Orchestration et cloud'
famille: engineering
resume: "Chaque matin, un graphe Airflow va chercher au BODACC les créations, transferts, ventes de fonds, radiations et défaillances d'entreprises de l'Hérault, puis les range en Parquet sur Amazon S3. Quand un contrôle de qualité échoue, rien n'est publié."
statut: publie
motif: pipeline
stack: ['Python', 'Apache Airflow', 'Amazon S3', 'Terraform', 'DuckDB', 'Athena', 'LocalStack', 'Parquet']
liens:
  github: 'https://github.com/yzasmin/radar-entreprises-herault'
teaser: 'video/projets/radar-entreprises.mp4'
metriques:
  - { label: 'Événements typés, parution de référence', valeur: '475' }
  - { label: 'Défaillances repérées sur 11 parutions', valeur: '93' }
  - { label: 'Contrôles de qualité, dont 13 bloquants', valeur: '15' }
  - { label: 'Coût S3 mesuré, par mois', valeur: '0,0015 $' }
---

## Contexte et problème

Une équipe commerciale qui prospecte dans l'Hérault veut savoir qui ouvre, par commune et par
secteur, le jour même de la publication. Un bailleur de locaux professionnels et un cabinet qui
suit un portefeuille de clients cherchent l'inverse dans le même flux : qui déménage, et qui passe
devant un tribunal.

L'information existe et elle est publique. Le Bulletin officiel des annonces civiles et commerciales
publie chaque jour ouvré les créations, immatriculations, modifications, radiations, ventes de fonds
et jugements de procédure collective. Telle quelle, elle est inexploitable : elle arrive par annonce
juridique et non par entreprise ni par territoire, les champs qui portent le sens sont du JSON
sérialisé dans une colonne texte, le montant d'une vente de fonds est une phrase en français, et il
n'existe ni code commune ni code d'activité, seulement un nom de ville saisi à la main.

La famille d'avis est trompeuse elle aussi. Sur les onze parutions traitées, le type normalisé
« immatriculation » ne compte aucun événement : toutes les annonces de cette famille étaient en
réalité des transferts de siège. Les compter telles quelles aurait ajouté 177 fausses ouvertures aux
609 réelles, soit 29,1 % de surestimation.

J'ai donc construit la brique manquante : un graphe quotidien qui capte ce flux, le normalise, le
rattache à une commune et à un secteur, refuse de publier si les données ne passent pas les
contrôles, et livre des indicateurs interrogeables en SQL. C'est le seul projet du portfolio à
faire tourner un ordonnanceur, un stockage objet et un entrepôt de données.

## Données

| Source                                  | Ce qu'elle apporte                                                               | Licence                                                                          | Fréquence                       |
| --------------------------------------- | -------------------------------------------------------------------------------- | -------------------------------------------------------------------------------- | ------------------------------- |
| **BODACC**, API Explore v2.1 de la DILA | Les annonces légales du jour                                                     | Licence Ouverte (`FR-LO`)                                                        | Quotidienne, du mardi au samedi |
| **API Recherche d'entreprises**         | Code NAF, tranche d'effectif, date de création                                   | Données SIRENE et RNE sous Licence Ouverte 2.0, sans clé, 7 requêtes par seconde | Quotidienne                     |
| **geo.api.gouv.fr**                     | Les 341 communes de l'Hérault : code INSEE, codes postaux, population, centroïde | Licence Ouverte                                                                  | Annuelle                        |

J'ai mesuré la volumétrie le 24/09/2026 et je l'ai enregistrée dans
`results/sources_verifiees.json` : 50 778 718 annonces au total dans le jeu BODACC, dont
1 086 632 pour le seul département de l'Hérault. La parution du jour même était déjà interrogeable.

J'ai évalué deux autres sources avant de les écarter. Les fichiers stock SIRENE de data.gouv.fr
pèsent 2 210 114 710 octets en Parquet pour les établissements et ne bougent qu'une fois par mois :
un stock mensuel ne peut pas porter un radar quotidien, et 2,2 Go à télécharger puis filtrer à
chaque rafraîchissement ne tiennent pas sur un poste de 8 Go de mémoire vive. L'API SIRENE de
l'INSEE, elle, renvoie HTTP 401 sans jeton : elle demande un compte, et je la rebrancherai le jour
où j'aurai une clé.

Enfin, l'API ouverte plafonne sa pagination : une requête `?departement=34` renvoie
`total_results: 10000`, qui est le plafond et pas un comptage. C'est ce constat qui a décidé
l'architecture : le flux porte la liste, le référentiel ne fait qu'enrichir. Quelques centaines
d'appels par jour au lieu de 2,2 Go par mois.

## Approche

Un graphe Airflow quotidien, déclenché à 6 h du mardi au samedi, enchaîne huit tâches. Le graphe ne
contient aucune logique métier : chaque tâche appelle une fonction ordinaire de
`radar/pipeline.py`. Le pipeline se rejoue donc sans ordonnanceur, et les 135 tests unitaires
tournent sur une machine où Airflow n'est même pas installé.

1. **preparer** : vérifie l'accès au compartiment et rafraîchit le référentiel des 341 communes.
2. **extraire_bodacc** : les annonces du jour, écrites en bronze telles que la source les rend.
3. **enrichir_sirene** : une fiche SIRENE par SIREN cité, cinq fils parallèles sous un jeton partagé
   qui borne le débit global à 5 requêtes par seconde.
4. **construire_silver** : un événement typé par annonce, avec type normalisé parmi 14 valeurs,
   gravité, SIREN validé par clé de Luhn, commune du référentiel, section NAF, montant de vente.
5. **controles_qualite_silver** : 10 contrôles, 9 bloquants. Une exception ici arrête le graphe, et
   la couche gold n'est jamais publiée sur des données refusées.
6. **construire_gold** : indicateurs par commune et section NAF, plus la liste des signaux.
7. **controles_qualite_gold** : 5 contrôles de cohérence entre les deux couches.
8. **publier_indicateurs** : synthèse et requêtes SQL, exécutées par le moteur de l'entrepôt.

Je stocke les trois couches en Parquet partitionné sur S3, sous `radar/bronze/`, `radar/silver/` et
`radar/gold/`. Le chemin porte la clé de partition (`date_parution=AAAA-MM-JJ`), qui n'apparaît pas
dans le fichier : Athena refuse une colonne qui est aussi une colonne de partition, DuckDB échoue
sur un nom en double, et la valeur n'est stockée qu'une fois au lieu d'une fois par ligne. Un test
verrouille cette propriété.

![Les quinze contrôles de qualité du graphe, sortie réelle du run](/images/projets/radar-entreprises/controles-qualite.png)

## Choix techniques

| Choix                                                             | Plutôt que                              | Pourquoi                                                                                                                                                                                                                                         |
| ----------------------------------------------------------------- | --------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Airflow, avec un DAG vide de logique métier                       | Une tâche planifiée qui lance un script | Un script ne sait pas rejouer une date précise depuis `logical_date`, arrêter la chaîne à la tâche de contrôle, reprendre sans tout refaire, ni tenir un journal par tâche. Le code métier, lui, reste dans des fonctions testables sans Airflow |
| BODACC comme source principale, SIRENE en enrichissement          | Le stock SIRENE mensuel comme source    | Le stock pèse 2 210 114 710 octets et bouge une fois par mois : il ne peut pas porter un radar quotidien. Le BODACC est un vrai flux journalier, et l'API ouverte plafonne à 10 000 résultats, ce qui interdit d'énumérer un département         |
| Partitionnement Hive, clé hors du fichier Parquet                 | Une colonne de date dans le fichier     | C'est la seule forme qu'Athena et DuckDB lisent tous les deux, avec le même SQL. Elle divise aussi la taille : 954 123 octets de JSON brut deviennent 73 580 octets de Parquet Snappy, soit un facteur 12,97                                     |
| Contrôles bloquants, séparés des alertes                          | Un journal d'avertissements             | Est bloquant ce que le pipeline doit garantir lui-même ; est alerte ce qui dépend d'un tiers. Une panne de l'API d'enrichissement doit dégrader la ventilation par secteur, pas empêcher de publier les comptes par commune                      |
| Volumétrie calibrée sur les centiles mesurés de l'historique      | Un seuil autour de la médiane           | J'ai mesuré 60 jours réels : de 1 à 1 046 annonces par jour, médiane 425, cinquième centile 9. Un seuil à la médiane sur quatre refusait dix de ces soixante journées, toutes légitimes                                                          |
| Aucune reprise sur les tâches de contrôle                         | Les deux reprises par défaut            | Un échec de qualité est déterministe : le rejouer ne change rien. Mesuré en intégration continue, une parution refusée coûtait 1 676 s avec deux reprises et attente exponentielle, contre 22 s sans                                             |
| DuckDB en secours d'Athena, sur les mêmes Parquet                 | Athena seul                             | Le projet reste exécutable sans compte AWS, et le SQL reste standard : la bascule vers Athena ne demande aucune réécriture de requête                                                                                                            |
| LocalStack pour l'intégration continue, vrai S3 pour les chiffres | Une clé AWS dans un workflow public     | Un dépôt public avec une clé réelle en secret permet à n'importe qui d'exécuter ce qu'il veut sur le compte via une pull request                                                                                                                 |

## Résultats et métriques

Les chiffres ci-dessous viennent d'une exécution sur le vrai compartiment S3 `amzn-s3-seau`, région
`eu-north-1`, lancée depuis mon poste : douze parutions du BODACC rejouées une par une.
L'orchestration Airflow est prouvée ailleurs, par le workflow d'intégration continue, qui tourne
contre un S3 émulé par LocalStack, puisque ce dépôt est public et ne porte volontairement aucune clé
AWS. Athena n'a pas tourné : le moteur de requête exécuté est DuckDB, sur exactement les mêmes
fichiers Parquet.

| Mesure                                     | Valeur                                | Source                          |
| ------------------------------------------ | ------------------------------------- | ------------------------------- |
| Parutions rejouées sur le vrai S3          | 12, dont **11 publiées et 1 refusée** | `results/execution_aws.json`    |
| Durée totale, durée médiane d'une parution | 1 371 s, 136 s                        | `results/execution_aws.json`    |
| Annonces extraites, parution du 22/09      | 475                                   | `results/volumetrie.json`       |
| Événements typés (silver)                  | 475                                   | `results/volumetrie.json`       |
| Lignes d'indicateurs (gold)                | 275                                   | `results/volumetrie.json`       |
| Communes touchées ce jour-là               | 117                                   | `results/synthese.json`         |
| Rattachement à une commune du référentiel  | 99,79 %, soit 474 sur 475             | `results/controles_qualite.csv` |
| SIREN valide (clé de Luhn)                 | 100,00 %, soit 475 sur 475            | `results/controles_qualite.csv` |
| Rattachement à une section NAF             | 97,47 %, soit 463 sur 475             | `results/controles_qualite.csv` |
| Contrôles de qualité                       | 15, dont 13 bloquants, tous au vert   | `results/controles_qualite.csv` |
| Compression JSON vers Parquet Snappy       | facteur **12,97**                     | `results/volumetrie.json`       |
| Volume écrit sur S3                        | 17 592 164 octets, 148 objets         | `results/volumetrie.json`       |
| Tests                                      | 135 passés, 4 sautés hors conteneur   | `results/pytest.txt`            |

C'est la parution refusée qui m'a le plus servi. Le 16 septembre, le BODACC n'avait publié qu'une
seule annonce pour l'Hérault. Le contrôle de volumétrie l'a vue, la tâche a levé, et la couche gold
n'est pas sortie. Le journal donne le motif en une ligne, avec l'attendu, l'observé et la façon dont
l'attendu a été calculé.

![Les douze communes les plus actives de l'Hérault le 22 septembre 2026](/images/projets/radar-entreprises/communes.png)

Sur onze parutions, du 8 au 23 septembre : 3 589 événements, 233 communes touchées,
1 102 couples commune-secteur, 609 créations, 362 radiations, 93 défaillances dont
62 liquidations, 29 redressements, 1 sauvegarde et 1 plan de cession, 177 transferts,
39 ventes de fonds, solde net +185 (`results/resume_fenetre.json`).

![Ouvertures et défaillances cumulées par commune sur la fenêtre](/images/projets/radar-entreprises/fenetre-glissante.png)

Côté facture, tarifs publics `eu-north-1` relevés le 24/09/2026 : 0,023 USD par Go et par mois.
Le radar a écrit 0,0176 Go, soit 0,000405 USD de stockage par mois, et 0,0015 USD par mois
tout compris, écritures et lectures incluses. Projeté sur un an de collecte quotidienne :
0,3998 Go, soit 0,0092 USD par mois (`results/cout_s3.json`).

![Solde net par section NAF, parution du 22 septembre](/images/projets/radar-entreprises/secteurs.png)

## Impact métier

Chaque chiffre ci-dessous est soit mesuré, soit donné comme une estimation accompagnée de
l'hypothèse qui la produit. Je n'avance aucun gain en euros.

### Ce qui est mesuré

| Signal livré                      | Valeur mesurée                                                                                                                                             | Ce qu'un utilisateur en fait                                                                                                                                         | Source                                                             |
| --------------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------- | -------------------------------------------------------------------------------------------------------------------------------------------------------------------- | ------------------------------------------------------------------ |
| **Défaillances sur 11 parutions** | 93, dont 62 liquidations                                                                                                                                   | Une liste nominative d'entreprises en procédure collective, avec commune, secteur, tribunal et date de jugement. C'est la matière d'une revue de portefeuille client | `results/resume_fenetre.json`                                      |
| **Concentration géographique**    | Montpellier porte 50 des 93 défaillances, soit 53,8 %, pour 178 créations. Aucune autre commune ne dépasse 3                                               | Dit où concentrer une revue de risque, et où elle ne sert à rien                                                                                                     | `results/fenetre_par_commune.csv`                                  |
| **Secteur le plus tendu**         | Construction : 21 défaillances pour 12 créations, seul grand secteur à solde négatif. Hébergement-restauration suit à 16 contre 18. Commerce : 14 pour 132 | Un critère sectoriel pour pondérer un encours ou cibler une prospection                                                                                              | `results/fenetre_par_secteur.csv`                                  |
| **Prospects du jour**             | 121 créations le 22 septembre, avec dénomination, SIREN, commune et activité déclarée                                                                      | Une liste de prospection à jour le jour même de la parution                                                                                                          | `results/synthese.json`, `results/requete_prospects_creations.csv` |
| **Ventes de fonds**               | 8 le 22 septembre, dont 5 avec un prix lisible, 1 374 086 euros cumulés                                                                                    | Un signal de changement d'exploitant, utile à un bailleur comme à un fournisseur                                                                                     | `results/synthese.json`, `results/requete_ventes_de_fonds.csv`     |
| **Fraîcheur**                     | La parution du jour même est interrogeable ; le graphe traite une parution en 136 s en médiane                                                             | Le délai entre publication légale et disponibilité de l'indicateur est celui du graphe, pas celui d'un abonnement                                                    | `results/execution_aws.json`                                       |
| **Coût d'exploitation**           | 0,0015 USD par mois aujourd'hui, 0,0092 après un an                                                                                                        | À l'échelle d'un département, le stockage ne pèse pas dans un arbitrage budgétaire                                                                                   | `results/cout_s3.json`                                             |

### Ce qui est une estimation, et son hypothèse

**Temps de dépouillement évité.** Le BODACC publie ses annonces en texte, une par une, sur son site.
Relever à la main les 3 589 annonces de ces onze parutions, en extrayant pour chacune le SIREN, la
commune et la nature de l'événement, prendrait de l'ordre de 20 à 30 secondes par annonce pour
quelqu'un qui connaît le format. **Hypothèse** : 25 secondes en moyenne, sans pause. Cela donne
environ 25 heures de travail pour ce que le graphe produit en 23 minutes cumulées
(1 371 secondes), un rapport d'environ 65 pour 1. C'est une estimation fondée sur une cadence
supposée : personne n'a chronométré ce dépouillement manuel.

**Erreur de comptage évitée.** Celle-ci est mesurée, pas estimée : compter les familles d'avis
brutes du BODACC aurait ajouté 177 fausses créations aux 609 réelles sur la fenêtre, soit
29,1 % de surestimation des ouvertures. Sans cette normalisation, un tableau de bord présente ces
transferts de siège comme des entreprises nouvelles.

### Ce que ce projet ne permet pas de dire

Le BODACC dit ce qui est publié, pas ce qui existe. Une entreprise en difficulté qui n'est pas
passée devant un tribunal n'y figure pas ; une micro-entreprise sans immatriculation au RCS non
plus ; une cessation d'activité sans radiation formelle reste invisible. Les 93 défaillances sont
donc un plancher, pas un décompte. Et ces chiffres ne sont pas comparables aux séries de
défaillances de l'INSEE ou de la Banque de France, qui ne comptent ni la même chose ni de la même
façon.

## Limites et pistes d'amélioration

Athena n'a pas tourné : LocalStack en édition communautaire ne l'émule pas, et je n'ai pas branché
le vrai service. Le SQL est écrit pour les deux moteurs, la déclaration des tables externes avec
projection de partition est dans le dépôt, la politique IAM correspondante aussi, mais je n'ai rien
exécuté de tout cela. Terraform non plus n'a pas été appliqué : seuls `fmt`,
`init -backend=false` et `validate` passent en intégration continue. Et l'orchestration et les
chiffres ne viennent pas du même endroit : le graphe Airflow tourne en CI contre LocalStack, les
chiffres viennent d'une exécution sur le vrai S3 lancée en ligne de commande. Les deux appellent
exactement les mêmes fonctions, mais il n'existe pas de capture d'Airflow pilotant le vrai
compartiment.

Par ordre de probabilité, voici ce qui casserait en production. La classification repose sur du
texte libre : un transfert se reconnaît au mot « transfert » dans le descriptif de l'acte, une
liquidation au texte du jugement. Si la DILA reformule ses libellés, le classement se dégrade sans
qu'aucun contrôle ne le voie : les comptes restent cohérents, ils comptent simplement autre chose.
C'est mon premier chantier, un contrôle de dérive. Viennent ensuite la dépendance à une API tierce
sans engagement de service, pour laquelle un cache par SIREN serait la première optimisation utile ;
la relecture complète d'une journée à chaque exécution, acceptable à 475 annonces par jour pour un
département mais pas à l'échelle nationale, où il faudrait sortir du plafond de pagination de
10 000 lignes ; et l'absence totale de supervision, un blocage n'étant visible aujourd'hui que dans
l'interface d'Airflow.

Dans l'ordre, les suites utiles : un contrôle de dérive sur la répartition des types, un cache
SIREN, une alerte sur échec de tâche, et seulement après, brancher Athena et appliquer Terraform
sur le vrai compte.
