Passer de LocalStack au vrai S3 n'a demande aucune ligne de code metier, mais trois surprises : la region du compartiment n'etait pas celle qu'on supposait, une cle au droit minimal fait echouer `list_buckets` et `head_bucket`, et DuckDB a besoin qu'on lui passe la cle autrement que boto3.

# Ce que la premiere bascule sur un vrai compte AWS a appris

Le code avait ete ecrit contre LocalStack avec une regle : **une seule variation,
`AWS_ENDPOINT_URL`**. La bascule l'a confirmee, et a revele trois choses qu'un emulateur ne peut pas
apprendre, parce qu'un emulateur dit oui a tout.

## 1. La region du compartiment se verifie, elle ne se suppose pas

Le projet avait ete ecrit pour `eu-west-3` (Paris), par raisonnement : donnees francaises, lecteur
francais, region la plus proche. Le compartiment reel etait en **`eu-north-1`** (Stockholm).

Une erreur de region ne donne pas un message clair. Selon l'appel, on obtient un `PermanentRedirect`,
un `AuthorizationHeaderMalformed` qui parle de la signature, ou, pire, une lenteur inexpliquee.
Le reflexe a prendre : **lire la region avant d'ecrire quoi que ce soit**, par
`get_bucket_location`, et la traiter comme une donnee de configuration, jamais comme une constante.
Le projet la lit dans `AWS_REGION` ou `AWS_DEFAULT_REGION`, dans cet ordre, parce que l'outil `aws`
connait le second nom et boto3 les deux.

## 2. Une cle au droit minimal fait echouer des appels qu'on croit anodins

Le diagnostic du projet affichait la liste des compartiments visibles. Sur le vrai compte, premier
appel, premiere erreur :

```
AccessDenied: User: arn:aws:iam::***:user/portfolio-airflow is not authorized to
perform: s3:ListAllMyBuckets because no identity-based policy allows the action
```

**Ce n'est pas un defaut de la cle, c'est la preuve qu'elle est bien etroite.** Une cle applicative
n'a aucune raison de savoir quels autres compartiments existent sur le compte. Meme chose pour
`head_bucket`, qui renvoie `403` quand la politique n'autorise `ListBucket` que sous un prefixe.

La lecon vaut au-dela de S3 : **un code ecrit contre un compte d'administrateur casse contre une cle
de production.** Les deux appels ont ete rendus tolerants et le diagnostic affiche maintenant
`"seaux_visibles": "refuse (AccessDenied)"` a cote de `"lecture_du_prefixe": "ok"`. La sortie est
plus utile qu'avant : elle dit exactement ce que la cle peut et ne peut pas faire.

Meme raisonnement pour `assurer_seau` : un `403` sur `head_bucket` ne veut pas dire que le
compartiment manque, il veut dire que la cle n'a pas le droit de poser la question. Le code
enregistre l'information et continue au lieu de s'arreter.

## 3. DuckDB ne partage pas les identifiants de boto3

`boto3` lit `AWS_ACCESS_KEY_ID` et `AWS_SECRET_ACCESS_KEY` dans l'environnement sans qu'on lui
demande rien. DuckDB, non : il faut lui creer un `SECRET`.

```sql
-- Vrai compte, cle deja dans l'environnement
CREATE OR REPLACE SECRET radar (TYPE S3, KEY_ID '...', SECRET '...', REGION 'eu-north-1');

-- Vrai compte, aucune cle dans l'environnement (profil, role) : extension aws requise
INSTALL aws; LOAD aws;
CREATE OR REPLACE SECRET radar (TYPE S3, PROVIDER credential_chain, REGION 'eu-north-1');

-- LocalStack : adressage par chemin et pas de TLS, sinon la lecture echoue
CREATE OR REPLACE SECRET radar (
  TYPE S3, KEY_ID 'test', SECRET 'test', REGION 'eu-north-1',
  ENDPOINT 'localstack:4566', URL_STYLE 'path', USE_SSL false
);
```

`PROVIDER credential_chain` demande l'extension `aws` en plus de `httpfs`, qui se telecharge au
premier usage : sur une machine sans acces reseau sortant, cela echoue tard et mal. Quand la cle est
deja dans l'environnement, la passer explicitement evite cette dependance.

## 4. Ce qui n'a PAS change, et c'est le resultat principal

- Les appels boto3, a l'identique : `boto3.client("s3", endpoint_url=cfg.endpoint_url)` avec
  `endpoint_url=None` sur le vrai compte.
- Les chemins : `radar/bronze/`, `radar/silver/`, `radar/gold/`, memes noms, memes partitions Hive.
- Les fichiers Parquet, le SQL, les controles de qualite, les tests.
- Le seul reglage conditionnel du projet reste l'adressage S3 par chemin, que LocalStack exige et que
  le vrai AWS accepte.

Ecrire contre un emulateur **sans jamais coder pour lui** est possible, a condition de poser la regle
au debut et de ne jamais la relacher « juste pour cette fois ».

## 5. Ne jamais mettre la vraie cle dans un workflow public

Le workflow d'integration continue de ce depot est public et ne porte **aucun secret**. Il tourne
contre LocalStack, et le dit. La cle reelle ne vit que dans un `.env` ignore par git, sur le poste.
Publier un badge vert obtenu avec une vraie cle sur un depot public, c'est offrir a n'importe qui la
possibilite de faire tourner ce qu'il veut sur ce compte en ouvrant une pull request.
