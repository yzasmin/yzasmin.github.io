Pièges HyperFrames rencontrés : apostrophe dans les variables, fromTo dans deux branches, texte transparent, lettres masquées.

- `data-composition-variables` est entre apostrophes simples : une apostrophe dans un libellé (`Couleur d'accent`) casse le JSON (`invalid_composition_variables_declaration`). Reformuler le libellé.
- L'analyse statique voit les `tl.fromTo` des deux branches d'un `if` sur le même objet proxy et signale `overlapping_gsap_tweens` : faire un seul tween dont la durée et le `onUpdate` dépendent de la condition, avec `immediateRender: false`.
- Un texte HTML en `color: transparent` avec `-webkit-text-stroke` déclenche l'erreur `text_not_painted` : utiliser un `<text>` SVG avec `fill: none; stroke: ...`.
- Lettres cachées par `yPercent` dans un conteneur `overflow: hidden` : le contrôle les signale `text_occluded`. Ajouter `opacity: 0` à l'état de départ.
- Un conteneur `.clip` qui englobe toute la scène déclenche `nested_structure_needs_subcomposition` ; une composition sans clip se rend très bien.
- `fitTextFontSize` ignore `letter-spacing` : garder une marge sur `maxWidth`.
