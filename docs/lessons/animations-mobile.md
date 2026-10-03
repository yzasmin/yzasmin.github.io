Sur téléphone, les apparitions au défilement ne valent pas le risque : un relecteur a vu des blocs vides là où il attendait les projets.

# Pourquoi les animations s'arrêtent à 768 px

Un développeur qui relisait le portfolio a signalé que la page « n'est pas responsive sur mobile »,
capture d'écran à l'appui : la section Projets apparaissait vide. Le site s'affiche pourtant
correctement quand on fait défiler normalement (vérifié à 375 px, les sept cartes mesurent entre
616 et 701 px de haut). Le vide venait des apparitions GSAP, qui masquent un bloc jusqu'à ce que le
défilement le déclenche. Une capture pleine page, un script lent ou un défilement rapide suffisent
à figer cet état intermédiaire.

Un visiteur qui voit du vide ne cherche pas la cause, il ferme l'onglet. Les animations sont donc
limitées à `(min-width: 768px)` dans `src/scripts/motion.ts`, et le masquage initial du hero suit
le même seuil dans `src/styles/global.css`. Les deux doivent rester synchronisés : si l'un bouge
sans l'autre, le nom reste invisible sur téléphone.

Effet secondaire utile : sur mobile, plus rien ne dépend du JavaScript pour afficher le contenu.
