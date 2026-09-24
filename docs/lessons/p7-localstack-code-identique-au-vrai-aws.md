Developper contre LocalStack sans creer de dette : un seul point de variation, `AWS_ENDPOINT_URL`, et deux reglages boto3 (adressage par chemin, absence de SSL) qui se deduisent de sa presence.

# Emuler S3 sans ecrire un code qui ne marchera que sur l'emulateur

Le risque d'un emulateur est d'ecrire un code qui lui est specifique, puis de tout reprendre le jour
ou le vrai compte arrive. La regle tenue ici : **le code ne sait pas a qui il parle**.

```python
def client_s3(cfg):
    reglages = BotoConfig(
        region_name=cfg.region,
        retries={"max_attempts": 5, "mode": "standard"},
        s3={"addressing_style": "path"} if cfg.est_emule else {},
    )
    return boto3.client("s3", endpoint_url=cfg.endpoint_url, config=reglages)
```

- `endpoint_url=None` : boto3 resout l'adresse publique du service. C'est **exactement** l'appel du
  vrai AWS, sans branche `if`.
- `est_emule` vaut simplement `endpoint_url is not None`. Une seule variable d'environnement decide.
- `addressing_style: path` est necessaire pour LocalStack, qui ne gere pas `seau.s3.amazonaws.com`.
  C'est le seul reglage specifique du projet, et il est adosse a la meme condition.
- Pour DuckDB, meme principe : `CREATE SECRET ... ENDPOINT 'localstack:4566', URL_STYLE 'path',
  USE_SSL false` contre l'emulateur, `PROVIDER credential_chain` contre le vrai compte.

## Ce que LocalStack en edition communautaire ne fait pas

S3 est emule, **Athena et Glue ne le sont pas**. Un projet qui pretend demontrer Athena sur LocalStack
communautaire ment. Le choix assume ici : le meme fichier SQL est execute soit par Athena sur le
catalogue Glue, soit par DuckDB sur les memes Parquet. Cela oblige a un SQL standard, sans fonction
propre a un moteur, ce qui est de toute facon une bonne chose.

## Trois choses a ne pas oublier

1. **La cle de partition ne doit pas figurer dans le fichier Parquet.** Athena refuse une colonne qui
   est aussi une colonne de partition. On la porte donc par le chemin (`date_parution=2026-09-22/`),
   et on la relit cote Python. C'est aussi ce qui permet a DuckDB de la reconstruire avec
   `hive_partitioning = true`, et de stocker la valeur une fois au lieu d'une fois par ligne.
2. **La projection de partition Athena evite un `MSCK REPAIR TABLE` apres chaque execution.**
   `projection.date_parution.type = 'date'` plus `storage.location.template` suffisent : Athena deduit
   les partitions de la plage declaree, et le graphe n'a aucune commande de catalogue a lancer.
3. **`${date_parution}` du DDL Athena n'est pas un champ de formatage Python.** Un
   `instruction.format(bucket=...)` sur ce DDL leve `KeyError: 'date_parution'`. Remplacement litteral
   obligatoire.
