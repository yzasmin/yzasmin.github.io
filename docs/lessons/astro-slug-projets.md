Avec le loader `glob` d'Astro, le champ `slug` du frontmatter fixe l'adresse de la page : les fichiers peuvent garder un préfixe numérique pour le tri.

# Adresses des fiches projets

Les fichiers s'appellent `01-projet-fin-etudes.md`, `02-challenge-kaggle.md`, etc. pour rester triés
dans l'explorateur. Sans `slug`, l'identifiant serait `01-projet-fin-etudes` et l'adresse
`/projets/01-projet-fin-etudes/`. Avec `slug: projet-fin-etudes`, le build génère
`/projets/projet-fin-etudes/` (vérifié dans la sortie de `astro build`).

L'ordre d'affichage vient du champ `ordre`, pas du nom de fichier.
