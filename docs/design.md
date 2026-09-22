# Direction artistique : « Carnet de figures »

Le site se lit comme un carnet de recherche data : chaque section est une figure numérotée
(`FIG. 01 / À PROPOS`), avec axes, graduations et annotations en police mono.
Détail mémorable : le parcours est un diagramme de Gantt 2019 à 2026, et le fond est un papier millimétré.

## Public et objectif

Recruteurs et responsables techniques, 30 secondes pour comprendre : qui (nom, rôle, lieu),
ce qu'elle maîtrise (stack par catégorie), preuves (chiffres, projets ouvrables).

## Couleurs (tokens CSS sur :root)

| Token     | Clair   | Sombre  | Usage                                    |
| --------- | ------- | ------- | ---------------------------------------- |
| --paper   | #F3F1EA | #0D0F12 | fond                                     |
| --paper-2 | #E9E6DC | #15181D | surfaces                                 |
| --ink     | #14161A | #ECE8DF | texte                                    |
| --ink-2   | #4A4F57 | #A3A8B0 | texte secondaire                         |
| --rule    | #C9C4B6 | #2A2F37 | filets, grille                           |
| --signal  | #C2185B | #FF5C8F | accent principal (magenta, repris du CV) |
| --teal    | #096B61 | #3FD1BE | série 2                                  |
| --amber   | #874F00 | #F2B84B | série 3                                  |
| --indigo  | #3949AB | #8C9EFF | série 4                                  |

## Typographie

- Titres : Bricolage Grotesque (variable), graisse 700 à 800, interlettrage serré, très grande taille.
- Texte : IBM Plex Sans 400/500.
- Annotations, labels d'axes, chiffres : IBM Plex Mono 400/500, en capitales espacées.

## Motifs

- Grille millimétrée en fond (lignes --rule à faible opacité, pas de 24 px, lignes majeures tous les 120 px).
- Graduations d'axe (ticks) en bord de figure, crochets d'angle (coins de viseur) sur les médias.
- Points de données (cercles pleins 6 px) aux couleurs des séries.
- Pas de dégradés violets, pas de blobs, pas de cartes imbriquées, pas de barres de niveau.

## Mouvement

GSAP + ScrollTrigger : tracé des axes et des filets, apparition des chiffres (compteurs), barres du Gantt qui croissent.
Sobre et rapide (0,6 à 0,9 s, ease power3.out). `prefers-reduced-motion` : tout est affiché directement, la vidéo ne se lance pas seule.

## Vidéo (HyperFrames)

1920x1080, 30 images par seconde, fond sombre (#0D0F12), mêmes polices et couleurs.
