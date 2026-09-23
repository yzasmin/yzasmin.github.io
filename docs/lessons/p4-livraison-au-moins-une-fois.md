Une livraison « au moins une fois » devient idempotente si la clé métier est unique en base : valider les décalages Kafka après le COMMIT, jamais avant.

# Au moins une fois plus clé unique égale exactement une ligne

- Le consommateur désactive `enable.auto.commit` et appelle `commit(asynchronous=False)` seulement après
  le COMMIT PostgreSQL du lot. Une panne entre les deux fait relire le lot : c'est voulu.
- La table `raw.station_status` porte `UNIQUE (station_id, last_reported)` et l'insertion utilise
  `ON CONFLICT ... DO NOTHING`. Les relectures ne créent donc pas de doublon ; le nombre de lignes
  réellement insérées est lu dans `cur.rowcount`, et l'écart avec la taille du lot est journalisé
  dans `raw.ingest_batches` comme doublons écartés. Le chiffre publié est mesuré, pas supposé.
- La déduplication interne au lot (`dedup_batch`) sert à éviter des allers-retours inutiles vers la base
  quand le même instantané est republié ; elle ne remplace pas la contrainte d'unicité.
- Côté producteur, `enable.idempotence` évite qu'un réessai réseau ne duplique un message dans le topic ;
  cela ne dispense pas de la contrainte en base, qui couvre aussi les relectures du consommateur.
- Mesurer deux latences distinctes aide au diagnostic : `ingested_at - last_reported` (bout en bout, inclut
  l'âge du flux, jusqu'à 60 s ici) et `ingested_at - fetched_at` (part propre au pipeline). Une seule
  valeur empêche de dire si le retard vient de l'opérateur ou du code.
