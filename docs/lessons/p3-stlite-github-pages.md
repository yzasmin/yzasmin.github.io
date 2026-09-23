Une app Streamlit se publie sans serveur ni compte tiers avec stlite (`@stlite/browser`) sur GitHub Pages : une page HTML qui monte le script et ses CSV par URL.

# Publier une démo Streamlit sur GitHub Pages avec stlite

- Page type (`pages/demo/index.html` du projet 3) : CSS et module depuis
  `https://cdn.jsdelivr.net/npm/@stlite/browser@1.9.1/build/stlite.{css,js}`, puis `mount({ requirements,
  entrypoint, files, streamlitConfig }, element)`.
- Les fichiers se montent par URL relative : `files["data/ind_annee.csv"] = { url: "./data/ind_annee.csv" }`.
  Le script Python les lit avec `Path(__file__).resolve().parent / "data"`, donc la **même application tourne en
  local** avec `uv run streamlit run app/streamlit_app.py`, sans branche conditionnelle.
- Le site est assemblé par un workflow (`actions/upload-pages-artifact` puis `actions/deploy-pages`) qui copie
  `pages/`, `app/streamlit_app.py`, `app/data/*.csv` et le notebook converti en HTML dans `_site/`.
  Activation côté dépôt : `gh api -X POST repos/<user>/<repo>/pages -f build_type=workflow`.
- Altair et pandas sont déjà disponibles dans l'environnement Pyodide de stlite ; les agrégats en CSV évitent de
  dépendre de pyarrow. Garder les fichiers légers (environ 150 Ko ici) : ils sont téléchargés par le navigateur.
- Coût réel : le premier chargement télécharge Pyodide et Streamlit, environ 30 Mo, soit quelques dizaines de
  secondes. Prévoir un message d'attente dans la page et le dire dans le README.
- Vérification : la page doit répondre 200 **et** afficher l'interface. Ici, contrôle par le navigateur intégré
  (titre, tuiles de métriques, onglets) après le déploiement, pas seulement un code HTTP.
