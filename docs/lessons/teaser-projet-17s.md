Le teaser de projet fait 17 s : la scène du gabarit, puis une scène qui montre un vrai graphique du projet, la seconde métrique et l'adresse du dépôt.

# Teaser de projet, version longue

`Portfolio/video/teaser-projet/` est la copie étendue de `teaser-template`. Le gabarit d'origine de 8 s
reste intact comme référence.

- Durée portée à 17 s sur la racine (`data-duration="17"`), scène 1 inchangée jusqu'à 6,6 s, sortie vers
  le haut de 6,6 à 7,2 s, scène 2 de 7,2 à 16,2 s, fondu final à 16,25 s pour une boucle propre.
- Nouvelles variables : `metric2Label`, `metric2Value`, `figure`, `figureCaption`, `repo`.
- L'image du graphique est posée avec `data-var-src` et `data-layout-allow-overflow` : sans cet attribut,
  le contrôle de mise en page signale un débordement de 5,8 px, dû au léger zoom de l'apparition.
- Le compteur de la métrique est devenu une fonction `countUp(tl, el, raw, at)` appelée deux fois, avec
  un objet de progression distinct par appel : un seul `fromTo` par objet, ce qui évite l'erreur
  `overlapping_gsap_tweens` de l'analyse statique.
- Les éléments de la scène 2 sont invisibles au départ grâce à l'état de départ des `fromTo`
  (`immediateRender` par défaut), sans `set` supplémentaire.
- L'image est attendue avec `img.decode()` dans le `Promise.all` des polices, sinon la première image
  rendue peut être vide.

Rendu d'un projet, depuis Git Bash : `cd "Portfolio/video/teaser-projet" && ./render.sh <slug>`.
Le script lit `projets-data/<slug>/teaser/variables.json`, prend le premier PNG du dossier, ajoute
`figure` et `repo` (lu dans le remote git du projet), rend en `--workers 1` vers
`site/public/video/projets/<slug>.mp4`, puis contrôle avec ffprobe. Compter environ 1 min 10 par teaser
et 2 Mo par fichier en CRF 18.

Piège rencontré : `here="$(cd "$(dirname "$0")" && pwd)"` donne un chemin `/c/...` que ni git ni python
ne comprennent sur Windows. Utiliser `pwd -W`, voir `hyperframes-ffmpeg-static.md`.
