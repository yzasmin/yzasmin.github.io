Un motif `data/` dans .gitignore ignore tous les dossiers `data` du dépôt, y compris `app/data` : ancrer avec `/data/` quand seul le dossier racine doit être exclu.

# Motifs .gitignore non ancrés

- Cas rencontré (projet 3) : `.gitignore` contenait `data/` pour exclure les fichiers DVF téléchargés. Résultat,
  `app/data/*.csv` (les agrégats publiés, indispensables à la démo) n'était pas versionné, et `git status` ne le
  signalait pas : les fichiers ignorés n'apparaissent pas.
- Règle : un motif sans barre oblique initiale s'applique à **tous les niveaux**. `/data/` ne vise que la racine du
  dépôt. Idem pour la négation : `!/data/.gitkeep`.
- Contrôle rapide avant le premier push : `git status --short | grep <chemin attendu>`, ou
  `git check-ignore -v app/data/ind_annee.csv` qui affiche la ligne du `.gitignore` responsable.
- Vérifier aussi ce qui **ne doit pas** partir : ici les ventes ligne à ligne (`data/processed/powerbi/`), que les
  conditions d'utilisation DVF interdisent de publier de façon indexable.
