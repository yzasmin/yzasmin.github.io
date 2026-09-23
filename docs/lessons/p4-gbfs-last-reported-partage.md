Dans le flux GBFS Vélomagg, toutes les stations partagent le même `last_reported` : la clé de déduplication doit rester (station_id, last_reported), et une interrogation sur deux peut ne rien apporter.

# Ce que le flux GBFS de Montpellier contient vraiment

- Flux : `https://gbfs.theta.fifteen.eu/gbfs/2.2/montpellier/en/gbfs.json`, GBFS 2.2, `ttl` = 60,
  52 stations, licence ODbL 1.0 annoncée dans `system_information.json`.
- Dans `station_status.json`, les 52 stations portent la même valeur de `last_reported`, égale au
  `last_updated` de l'enveloppe : l'opérateur publie un instantané complet, il ne date pas chaque station.
  Conséquence : `last_reported` ne mesure pas la fraîcheur par station, et la clé naturelle de
  déduplication est bien le couple (station_id, last_reported), pas `last_reported` seul.
- Interroger toutes les 60 secondes ne se cale pas sur la publication : sur 309 interrogations réelles,
  308 instantanés distincts et 1 interrogation redondante (`results/archive_synthese.json`).
  L'âge médian du flux au moment de la lecture était de 35,9 s, au maximum 60,0 s : c'est le plancher
  incompressible de la latence de bout en bout, avant même le pipeline.
- Écart médian entre deux instantanés : 60 s, maximum 64 s, aucun intervalle au-dessus de 90 s :
  aucune mise à jour n'a été manquée sur la période, mais un décalage de la boucle suffirait à en sauter une.
- `capacity` de `station_information` n'est pas toujours égal à `num_bikes_available + num_docks_available`
  (bornes hors service) : tester `vélos <= capacité`, pas l'égalité de la somme.
