Les huit questions qu'un responsable technique pose sur ce projet, et les reponses fondees sur ce qui a reellement tourne.

# Entretien : radar economique des entreprises de l'Herault

Depot : https://github.com/yzasmin/radar-entreprises-herault
Chaque chiffre cite ici se retrouve dans un fichier de `results/`, produit par le run d'integration
continue dont le numero est donne dans le README.

## 1. Pourquoi Airflow ici, alors qu'une tache planifiee ferait la meme chose ?

Une tache planifiee sait lancer un script. Elle ne sait pas faire les quatre choses pour lesquelles
j'ai pris un ordonnanceur.

1. **Rejouer une date precise.** Le graphe ne lit jamais `date.today()`. Il lit `logical_date`, et
   accepte un parametre `jour`. Relancer la parution du 17 septembre retraite exactement cette
   parution, avec les memes donnees et les memes chiffres. C'est ce qui m'a permis de rejouer onze
   journees d'archive pour remplir la fenetre glissante, une par une, avec le meme code.
2. **Arreter la chaine au bon endroit.** La tache `controles_qualite_argent` leve une exception quand
   un controle bloquant echoue. Les taches en aval ne partent pas : la couche or n'est jamais
   publiee sur des donnees refusees. Avec un script unique, il faudrait coder ce garde-fou a la main,
   et se souvenir de le remettre a chaque ajout d'etape.
3. **Reprendre sans tout refaire.** Chaque tache a deux reprises avec attente exponentielle plafonnee
   a trente minutes. Une coupure reseau pendant l'enrichissement ne reprend que l'enrichissement,
   parce que la couche bronze est deja ecrite sur S3.
4. **Savoir ce qui s'est passe.** Un journal par tache et par execution, une duree par tache, un etat
   par date. Un script shell donne une sortie melangee et une seule duree.

Ce qu'Airflow **n'apporte pas**, et c'est pourquoi le DAG est vide de logique : il n'execute rien de
metier. Chaque tache appelle une fonction de `radar/pipeline.py`. Le pipeline tourne sans
ordonnanceur (`python -m radar.cli executer --jour 2026-09-22`), et les 126 tests unitaires tournent
sur une machine ou Airflow n'est meme pas installe.

## 2. Vous dites que vos controles de qualite sont bloquants. Comment le prouvez-vous ?

Trois preuves, dans cet ordre.

**Dans les tests.** `tests/test_quality.py` verifie chaque controle dans les deux sens : il passe
quand il doit passer, et il echoue quand il doit echouer. Un controle qui ne sait pas echouer ne
controle rien. `tests/test_pipeline_s3.py` va plus loin : il construit une partition avec une
volumetrie effondree, puis une avec une source gelee depuis sept semaines, et verifie qu'une
`QualiteError` est levee a la tache de controle, pas plus tard.

**Dans le run d'integration continue.** Une etape execute `scripts/preuve_blocage.py`, qui force
quatre situations anormales (volumetrie effondree, source gelee, annonce publiee deux fois,
evenements perdus entre l'argent et l'or) et exige que les quatre levent. La sortie est conservee
dans `results/preuve_blocage.txt`.

**Dans la realite, et c'est la meilleure preuve.** En rejouant onze parutions du BODACC, le graphe en
a refuse une. Le journal donne le motif en une ligne : la source avait publie une seule annonce pour
l'Herault ce jour-la, la ou la mediane des jours precedents est de plusieurs centaines. Ce n'est pas
un defaut du pipeline, c'est une anomalie reelle de la source, detectee par le controle. Je publie ce
blocage plutot que de desserrer le seuil jusqu'a ce que tout passe.

## 3. Sur quels controles, exactement, et pourquoi ceux-la ?

Neuf sur la couche argent, cinq sur la couche or. Six sont bloquants, les autres sont des alertes.
La separation suit une regle simple : **est bloquant ce que le pipeline lui-meme doit garantir, est
alerte ce qui depend d'un tiers**.

| Controle                             | Bloquant     | Ce qu'il attrape                                                                            |
| ------------------------------------ | ------------ | ------------------------------------------------------------------------------------------- |
| `unicite_id_annonce`                 | oui          | Le BODACC republie parfois le meme identifiant (avis rectificatif)                          |
| `cles_non_nulles`                    | oui          | Une annonce sans identifiant ou sans date casse la partition                                |
| `type_evenement_dans_le_vocabulaire` | oui          | Une nouvelle famille d'avis cote source, non traitee cote code                              |
| `partition_homogene`                 | oui          | Une ligne d'un autre jour dans la partition du jour                                         |
| `perimetre_departemental`            | oui          | Un code commune hors Herault, donc un rapprochement faux                                    |
| `volumetrie_dans_la_norme`           | oui          | Une collecte partielle, ou une source qui deraille                                          |
| `fraicheur_source`                   | oui          | Un flux arrete : sans ce controle, le graphe produirait des partitions vides sans rien dire |
| `rapprochement_communes`             | oui          | Une regression de la normalisation des noms de communes                                     |
| `presence_siren`                     | non (alerte) | Degradation de la qualite des identifiants                                                  |
| `grain_or_unique`                    | oui          | Un doublon d'agregation                                                                     |
| `conservation_argent_vers_or`        | oui          | Des evenements perdus entre deux couches                                                    |
| `coherence_solde_net`                | oui          | Une formule d'indicateur cassee                                                             |
| `compteurs_positifs`                 | oui          | Un compteur negatif, donc un bug d'agregation                                               |
| `couverture_naf`                     | non (alerte) | Une panne de l'API d'enrichissement                                                         |

Les deux alertes dependent de l'API Recherche d'entreprises, qui n'est pas la mienne. Si elle tombe,
les comptes par commune restent justes et seule la ventilation par secteur devient inconnue. Bloquer
la publication pour cela serait une erreur de conception.

## 4. Votre volumetrie est calibree comment ? Pas un seuil en dur, j'espere.

Non, et c'est volontaire. Le BODACC publie pour l'Herault entre une et plus de sept cents annonces
par jour selon la date : un seuil ecrit en dur serait faux le mois suivant.

Le controle lit l'historique **dans la source elle-meme**, sur les 90 jours precedant la date
traitee, par une seule requete d'agregation, puis compare la volumetrie du jour a la **mediane** de
cet historique, avec un facteur quatre de part et d'autre. Trois details comptent :

- la mediane plutot que la moyenne, pour qu'une journee exceptionnelle ne deplace pas les bornes ;
- les jours sans parution sont **exclus** du calcul, sinon les dimanches feraient tomber la mediane et
  le controle ne detecterait plus rien ;
- quand l'historique fait moins de cinq jours, le controle se rabat sur « au moins une annonce » et
  l'ecrit dans sa ligne de sortie, au lieu de faire semblant de calibrer.

La ligne publiee donne l'attendu, l'observe et la facon dont l'attendu a ete calcule :
`entre 70 et 1120 annonces (mediane 280 sur 62 jours, facteur 4) | 1 annonces`. Elle se releve en dix
secondes. Une alerte qui dirait seulement « volumetrie anormale » se releve en vingt minutes.

## 5. Vous affichez AWS, mais vous n'aviez pas de compte. Qu'est-ce qui a reellement tourne ?

L'execution publiee est **emulee** : S3 est fourni par LocalStack, dans le meme `docker compose` que
l'ordonnanceur. Je l'ecris en tete du README et dans la fiche, parce qu'un lecteur qui le decouvre
apres coup a raison de se mefier de tout le reste.

Ce qui rend cette emulation honnete, c'est que **le code ne sait pas a qui il parle** :

```python
return boto3.client("s3", endpoint_url=cfg.endpoint_url, config=reglages)
```

`endpoint_url=None` est exactement l'appel du vrai AWS. Une seule variable d'environnement,
`AWS_ENDPOINT_URL`, decide. Le nom du compartiment est le meme des deux cotes (`amzn-s3-seau`), le
prefixe aussi, le partitionnement aussi, le Parquet aussi. Il n'y a pas de branche `if` sur le
fournisseur dans le code metier ; la seule condition liee a l'emulation est le style d'adressage S3
par chemin, que LocalStack exige et que le vrai AWS accepte.

La bascule est ecrite et prete : un fichier `.env` avec la cle, `AWS_ENDPOINT_URL` laissee vide, puis
`./scripts/bascule_aws.sh 2026-09-22`, qui rejoue le meme graphe sur le vrai compartiment.
Ce que je ne pretends pas : qu'Athena a tourne. LocalStack en edition communautaire n'emule ni Athena
ni Glue. Le SQL est ecrit pour les deux moteurs, la declaration des tables Athena est dans le depot
et la politique IAM aussi, mais le moteur reellement execute est DuckDB, sur les memes Parquet.

## 6. Montrez-moi votre politique IAM.

Elle est dans le depot, `infra/politique-minimale.json`, et elle est courte exprès.

- `s3:ListBucket` et `s3:GetBucketLocation` sur le seul compartiment, **avec une condition
  `s3:prefix` limitee a `radar/`**.
- `s3:GetObject`, `s3:PutObject`, `s3:AbortMultipartUpload`, `s3:ListMultipartUploadParts` sur
  `arn:aws:s3:::amzn-s3-seau/radar/*` et rien d'autre.
- Une troisieme instruction en `Deny` avec `NotResource`, qui refuse tout ce qui sort du compartiment
  et de son prefixe. Dans IAM, un refus explicite l'emporte toujours : meme si une politique plus
  large etait attachee un jour au meme utilisateur, le garde-fou tient.

Ce qui n'y est pas, volontairement : **aucune suppression**. Ni `s3:DeleteObject`, ni
`s3:DeleteBucket`. Le graphe ecrase une partition en reecrivant la meme cle, il n'a jamais besoin de
supprimer. Une cle qui ne sait pas supprimer ne peut pas vider un compartiment par accident. Il n'y a
pas non plus `s3:CreateBucket` : le compartiment existe deja.

Et il n'y a aucune cle dans le depot. Terraform cree l'utilisateur et les politiques mais **pas la
cle d'acces**, parce qu'une cle creee par Terraform finit en clair dans le fichier d'etat. Elle se
cree a la main dans la console, une fois, et va dans un `.env` ignore par git ou dans un secret
GitHub. `.env.example` est commite, `.env` ne l'est pas.

## 7. Comment le meme SQL peut-il tourner sur Athena et sur DuckDB ?

Parce que les deux lisent **les memes fichiers**, et que j'ai renonce a tout ce qui est propre a un
moteur.

Le partitionnement est de style Hive : `date_parution=2026-09-22/` dans le chemin, et **la colonne
`date_parution` n'est pas dans le fichier Parquet**. Athena refuse une colonne qui est aussi une
colonne de partition, DuckDB echoue sur un nom en double avec `hive_partitioning = true`, et la
valeur n'est stockee qu'une fois au lieu d'une fois par ligne. Un test verrouille cette propriete.

Cote Athena, `sql/athena_ddl.sql` declare deux tables externes avec **projection de partition**, ce
qui evite d'avoir a lancer `MSCK REPAIR TABLE` apres chaque execution. Cote DuckDB, deux vues du
meme nom sont creees sur `read_parquet(..., hive_partitioning = true)`. Ensuite, `sql/indicateurs.sql`
s'execute tel quel des deux cotes : pas de `QUALIFY`, pas de fonction de liste, pas de CTE recursive.

C'est une contrainte, et elle est saine : le jour ou la cle AWS arrive, la bascule ne demande aucune
reecriture de requete.

## 8. Qu'est-ce qui casserait en production, et que feriez-vous d'abord ?

Quatre choses, par ordre de probabilite.

1. **La classification des evenements repose sur du texte libre.** Un transfert se reconnait au mot
   « transfert » dans `acte.descriptif`, une liquidation au texte du jugement. Si la DILA reformule
   ses libelles, le classement se degrade **sans qu'aucun controle ne le voie** : les comptes restent
   coherents, ils comptent simplement autre chose. C'est la premiere chose que je traiterais, avec un
   controle de derive sur la repartition des types d'un jour a l'autre.
2. **L'enrichissement depend d'une API tierce sans engagement de service.** Elle accepte sept requetes
   par seconde et par adresse IP ; j'en fais cinq, avec un jeton partage entre cinq fils, ce qui donne
   un debit reel de cinq par seconde au lieu d'environ un en boucle sequentielle. Si elle tombe, la
   couverture NAF s'effondre et le controle passe en alerte. En production, je mettrais un cache par
   SIREN, parce que les memes entreprises reviennent d'une annonce a l'autre.
3. **Le graphe relit tout un jour a chaque execution.** C'est acceptable a 475 annonces par jour pour
   un departement. A l'echelle nationale, il faudrait sortir du plafond de pagination de 10 000 lignes
   de l'API Explore, donc decouper par famille d'avis, et passer a une ecriture par lots.
4. **Rien n'est surveille.** Pas d'alerte sur l'echec d'un graphe, pas de tableau de bord, pas de
   destinataire. Aujourd'hui, un blocage se voit dans l'interface d'Airflow et nulle part ailleurs.
   En production, la premiere brique serait une notification sur echec de tache, avant meme toute
   nouvelle fonctionnalite.

Et une limite de fond, qui n'est pas technique : **le BODACC dit ce qui est publie, pas ce qui
existe**. Une entreprise en difficulte qui n'est pas passee devant un tribunal n'y figure pas, et une
creation de micro-entreprise sans immatriculation au RCS non plus. Le radar mesure un flux d'annonces
legales, pas l'economie reelle. Je le dis avant qu'on me le demande.
