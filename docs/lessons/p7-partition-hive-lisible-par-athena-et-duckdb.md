Ecrire une colonne a la fois dans le chemin de partition et dans le fichier Parquet casse Athena : la cle de partition se porte uniquement par le chemin, et se relit cote code.

# Une seule ecriture Parquet, deux moteurs de lecture

L'objectif : que le meme jeu de fichiers soit interrogeable par Athena quand un compte AWS existe, et
par DuckDB quand il n'en existe pas, **avec le meme texte SQL**.

## La regle qui fait tout tenir

Le chemin porte la cle :

```
s3://amzn-s3-seau/radar/silver/evenements/date_parution=2026-09-22/evenements.parquet
s3://amzn-s3-seau/radar/gold/indicateurs_commune_secteur/date_parution=2026-09-22/indicateurs.parquet
```

et **le fichier Parquet ne contient pas la colonne `date_parution`**. Trois raisons, pas une :

1. Athena refuse une table dont une colonne du fichier porte le meme nom qu'une colonne de partition.
2. DuckDB reconstruit la colonne avec `hive_partitioning = true` ; si elle existe aussi dans le
   fichier, la lecture echoue sur un nom de colonne en double.
3. La valeur est stockee une fois dans un nom de dossier au lieu d'une fois par ligne.

Cote code, la cle se relit a la lecture (`_avec_partition`), ce qui est exactement ce que fait le
moteur SQL. Un test le verrouille :

```python
table = lire_parquet(cfg, f"radar/silver/evenements/date_parution={jour}/evenements.parquet")
assert "date_parution" not in table.column_names
```

## Le SQL reste standard, et c'est une contrainte saine

Le fichier `sql/indicateurs.sql` est decoupe sur des marqueurs `-- nom: <cle>`, et chaque requete
s'execute telle quelle sur les deux moteurs. Cela interdit les fonctions propres a DuckDB
(`list_aggregate`, `QUALIFY`) comme celles propres a Trino. En echange, la bascule vers Athena ne
demande aucune reecriture, et le SQL reste lisible par quelqu'un qui ne connait ni l'un ni l'autre.

## Projection de partition : le detail qui evite une tache de catalogue

```sql
'projection.enabled' = 'true',
'projection.date_parution.type' = 'date',
'projection.date_parution.format' = 'yyyy-MM-dd',
'projection.date_parution.range' = '2024-01-01,NOW',
'storage.location.template' = 's3://.../date_parution=${date_parution}/'
```

Sans cela, il faut lancer `MSCK REPAIR TABLE` ou un `ALTER TABLE ADD PARTITION` apres chaque
execution, donc ajouter une tache au graphe et un droit `glue:BatchCreatePartition` a la cle.
Avec, Athena deduit les partitions de la plage declaree et le graphe n'a rien a faire.

Attention : `${date_parution}` dans ce DDL n'est pas un champ de formatage Python. Un
`instruction.format(bucket=...)` leve `KeyError: 'date_parution'`. Remplacement litteral obligatoire.

## Ce que cela coute en taille

Sur une journee reelle : **954 123 octets** de JSON brut deviennent **72 699 octets** de Parquet
compresse en Snappy, soit un facteur **13,1**. Sur Athena, ou la facturation se fait a l'octet balaye,
c'est directement treize fois moins cher par requete.
