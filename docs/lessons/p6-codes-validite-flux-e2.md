Dans le flux E2 de qualité de l'air, filtrer sur `validité == 1` supprime silencieusement six mois d'ozone : le code 4 est aussi une donnée valide.

# Codes de validité du flux E2

**Contexte.** Les fichiers E2 du LCSQA (data.gouv.fr) portent une colonne `validité` dont les valeurs
suivent le vocabulaire européen `observationvalidity` de l'EEA.

- `1` valide, `2` et `3` valides mais sous la limite de détection, `-1` et `-99` non valides.
- `4` : « Valid (Ozone only) using CCQM.O3.2019 », ajouté au vocabulaire en juillet 2024 pour les
  mesures d'ozone rapportées avec la nouvelle section efficace. Atmo Occitanie l'utilise à partir de
  janvier 2025, et exclusivement à partir de juillet 2025.

**Symptôme.** Avec un filtre `validité == 1`, le taux de manquants de l'ozone passait à 18 % et les
cinq stations d'ozone de l'Hérault paraissaient toutes arrêtées de juillet à décembre 2025, au même
moment. Une panne simultanée sur cinq stations est le signal qu'il faut soupçonner le filtre, pas les
stations : en l'occurrence 16,7 % des heures d'ozone de 2023-2025.

**Règle.** Conserver `validité %in% c(1, 2, 3, 4)`, et vérifier la liste des codes présents dans le
jeu de données avant d'écrire le filtre (`table(validité)`), plutôt que de recopier un filtre vu
ailleurs. Noter aussi la conséquence statistique : le changement de section efficace crée une rupture
de comparabilité de quelques pour cent dans les séries d'ozone.

**Voir aussi.** Les horodatages du flux E2 sont en temps universel pour la métropole ; sans conversion
en heure locale, les profils horaires sont décalés d'une à deux heures selon la saison.
