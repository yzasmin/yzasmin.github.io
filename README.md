# Portfolio de Yasmina Saoud

Site personnel (Data Scientist, Data Analyst, Data Engineer), construit avec Astro, Tailwind CSS et GSAP,
déployé sur GitHub Pages par GitHub Actions.

## Commandes

| Commande          | Effet                                                   |
| ----------------- | ------------------------------------------------------- |
| `npm install`     | Installe les dépendances (Node.js 22.12 ou plus)        |
| `npm run dev`     | Serveur local sur http://localhost:4321                 |
| `npm run lint`    | Vérification des types Astro et du formatage            |
| `npm run build`   | Génère le site statique dans `dist/`                    |
| `npm run preview` | Sert `dist/` en local pour contrôle avant mise en ligne |
| `npm run format`  | Reformate le code avec Prettier                         |

## Où modifier quoi

- Textes de présentation, stack, parcours, liens et CV : `src/data/profile.ts`.
- Fiches projets : `src/content/projets/*.md`, mode d'emploi dans `docs/gabarit-projet.md`.
- CV téléchargeables : `public/cv/` (une page du CV source par poste visé).
- Photo de profil : déposer le fichier dans `public/images/` sous le nom exact `portrait-yasmina.jpg`
  (`.jpeg`, `.png`, `.webp` ou `.avif` acceptés), cadrage portrait 4/5, au moins 800 × 1000 px, moins de 2 Mo.
  Tant que le fichier est absent, la section « À propos » s'affiche sans photo et occupe toute la largeur ;
  dès qu'il est présent, Astro l'optimise (AVIF/WebP, srcset) et l'affiche, sans autre modification de code.
- Vidéo du hero : `public/video/intro.mp4` et `intro-poster.jpg`, sources HyperFrames dans `../video/hero-intro/`.
- Teasers projets : gabarit HyperFrames dans `../video/teaser-template/`, rendus dans `public/video/projets/`.
- Direction artistique (couleurs, typographies, motifs) : `docs/design.md`.
- Leçons apprises : `docs/lessons/`.

## Déploiement

Chaque push sur `main` lance `.github/workflows/deploy.yml` : installation, `npm run lint`, `npm run build`,
puis publication de `dist/` sur GitHub Pages. Les pull requests exécutent seulement la vérification et le build.

Réglage à faire une fois dans le dépôt GitHub : Settings, Pages, Source : « GitHub Actions ».

Adresse : `https://yzasmin.github.io/` si le dépôt s'appelle `yzasmin.github.io`. Pour un autre nom de dépôt
(par exemple `portfolio`), mettre `base: '/portfolio'` dans `astro.config.mjs` et adapter `public/robots.txt`.

Retour arrière : `git revert` du commit fautif puis push, ce qui relance le déploiement.
