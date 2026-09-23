Les graphiques raster d'un poster PDF peuvent contenir plus que la partie visible : extraire l'image par xref avec pymupdf, pas une capture de page.

# Figures d'un poster PDF

- `page.get_images(full=True)` puis `page.get_image_rects(xref)` donnent chaque image et sa position.
  Sur le poster EMOTIC, l'image 166 était placée à x = -557 : son panneau de gauche (mAP 0,2511 et 0,2560)
  était hors de la page, invisible à l'écran mais présent dans le fichier.
- `pymupdf.Pixmap(doc, xref)` récupère l'image entière à sa résolution d'origine (1989 x 690), bien meilleure
  qu'un rendu de page. Convertir en RGB si `pix.n - pix.alpha > 3` (CMJN).
- Les schémas vectoriels (architecture) ne sont pas des images : les rendre avec `page.get_pixmap(clip=Rect, dpi=110)`.
- Découper un panneau : plus simple avec PIL (`Image.crop`) qu'avec le constructeur `Pixmap(src, w, h, clip)`.
- Vérifier chaque figure avant publication : aucune photo de personne issue du jeu de données.
- Script : `projets-data/projet-fin-etudes/scripts/extraire_figures_poster.py`.
