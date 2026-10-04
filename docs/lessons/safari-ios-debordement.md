Safari dimensionne une colonne de grille sur la largeur minimale d'un SVG enfant : sans `grid-cols-1` explicite, la page faisait 513 px de large sur un iPhone de 390 px.

# Débordement latéral sur iPhone, invisible dans Chrome

Yasmina a signalé que la section Projets « déborde en largeur » sur son iPhone 13, alors que Chrome,
même en émulation mobile, ne montrait rien : `document.scrollWidth` valait 375 px pour un écran de
375 px. L'émulation d'un téléphone dans Chrome reste Chrome ; seul un moteur WebKit révèle ce genre
d'écart.

## Comment reproduire

```bash
npm i -D playwright && npx playwright install webkit
```

Puis un script qui ouvre la page avec `webkit.launch()` et le profil `devices['iPhone 13']`, et qui
compare `document.documentElement.scrollWidth` à `clientWidth`, en listant les éléments dont le bord
droit dépasse. Mesure obtenue sur le site en ligne : 513 px de document pour 390 px d'écran, et 85
éléments hors cadre, tous dans les cartes de projets.

## Les deux causes

1. La grille des cartes s'écrivait `grid ... sm:grid-cols-2 lg:grid-cols-3`, sans colonne déclarée
   pour le téléphone. Chrome donne alors une colonne qui s'adapte ; Safari calcule sa largeur
   minimale à partir du contenu, ici le graphique SVG de la carte, et obtient 492 px. La classe
   `grid-cols-1` de Tailwind vaut `repeat(1, minmax(0, 1fr))`, ce qui autorise la colonne à se
   réduire. C'est la correction.
2. Les crochets de viseur (`.brackets::before` et `::after`) étaient posés à `-7px` du cadre, donc
   hors de lui. Safari compte ce dépassement dans la largeur du document. Ils restent à l'extérieur
   à partir de 768 px et passent à `0` en dessous.

Garde-fou ajouté au passage : `overflow-x: clip` sur `html` en plus de `body`, car Safari calcule la
largeur de page au niveau de `html`. `clip` et non `hidden` : `hidden` créerait un conteneur de
défilement et casserait l'en-tête collant.

## Règle à retenir

Toute grille déclare sa colonne mobile, et tout élément décoratif reste dans son cadre sous 768 px.
Avant de conclure qu'une page tient sur téléphone, la mesurer avec WebKit, pas seulement avec Chrome.
