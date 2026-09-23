Le chemin du poste contient une apostrophe (« Offre d'emploi ») : elle casse toute chaîne SQL DuckDB ; travailler en chemins relatifs après un `os.chdir`.

# DuckDB et les chemins contenant une apostrophe

- Symptôme : `SET temp_directory='C:/Users/33769/Desktop/Offre d'emploi/...'` renvoie
  `Parser Error: syntax error at or near "emploi"`. Même piège avec `read_csv('...')` et `COPY ... TO '...'`.
- Correctif retenu dans le projet 3 : `os.chdir(RACINE)` au début du pipeline, puis uniquement des chemins
  relatifs dans le SQL (`data/raw/dvf_34_*.csv.gz`, `results/...`), avec une fonction `relatif()` qui double aussi
  les apostrophes résiduelles.
- Autre solution possible : passer les chemins en paramètres (`$chemin`) plutôt que par interpolation de chaîne.
- Le même piège touche tout outil qui reçoit un chemin dans une chaîne : penser aux commandes shell, où il faut
  citer, et aux `.exe` Windows appelés depuis Git Bash, qui n'acceptent pas les chemins `/c/Users/...`.
