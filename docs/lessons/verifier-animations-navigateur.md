Vérifier les animations dans un Chrome réellement affiché, en défilant pour de vrai : un onglet masqué ralentit GSAP et une capture pleine page fige les apparitions.

# Vérifier le rendu animé

- Le panneau navigateur intégré, quand il est masqué, ralentit fortement `requestAnimationFrame` :
  une animation d'une seconde paraît bloquée à mi-course. Utiliser le Chrome piloté par les outils
  chrome-devtools pour les captures.
- Les apparitions `ScrollTrigger` ne se déclenchent qu'au défilement. Avant de juger, faire défiler
  la page par script (pas de 250 px, pause de 120 ms), puis mesurer : sur la page d'accueil,
  48 éléments `[data-reveal]` sont masqués au chargement et 0 après défilement.
- Une capture « pleine page » redimensionne la fenêtre et peut laisser des blocs vides : c'est un
  artefact de l'outil, pas un défaut du site. Préférer des captures de la fenêtre après défilement.
- Un thème enregistré dans `localStorage` (`theme`) prend le pas sur la préférence système :
  le vider avant de tester le mode sombre par émulation.
