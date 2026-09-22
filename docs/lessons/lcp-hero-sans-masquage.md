Ne jamais masquer en CSS l'élément LCP en attendant GSAP : sur mobile, le délai de rendu passait à 2,7 s et la performance Lighthouse à 84.

# LCP et animations d'entrée du hero

**Mesure.** Lighthouse mobile sur l'accueil compilé : performance 84, LCP 3,2 s, dont 2,7 s de « Render Delay ».
L'élément LCP était le paragraphe de présentation, masqué par `html.js [data-hero-fade] { visibility: hidden }`
jusqu'à l'exécution du bundle GSAP (44 Ko compressés, lent sous bridage CPU).

**Correction.** Retirer `data-hero-fade` du paragraphe (voir le commentaire dans `src/components/Hero.astro`)
et réduire l'image d'affiche de la vidéo de 1920 à 1280 px (181 Ko à 68 Ko).

**Résultat.** Performance 90 et 91 sur deux mesures, premier affichage 1,8 s, LCP 2,4 s, CLS proche de 0.

**Règle.** Avant d'animer un élément du premier écran, vérifier dans le rapport Lighthouse
(`largest-contentful-paint-element`) qu'il n'est pas l'élément LCP.
