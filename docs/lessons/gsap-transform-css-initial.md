Un `transform: translateY(105%)` posé en CSS s'additionne au `yPercent` de GSAP : toujours remettre `y: 0` dans le `fromTo`.

# GSAP et transformation CSS initiale

**Contexte.** Pour éviter un flash du nom avant l'animation du hero, les lettres sont masquées en CSS
(`html.js [data-hero-char] { transform: translateY(105%) }`), puis animées avec
`gsap.fromTo(..., { yPercent: 105 }, { yPercent: 0 })`.

**Symptôme.** Le nom restait invisible : la lettre finissait à `translate(0px, 162px)`.

**Cause.** GSAP lit la transformation CSS existante et la convertit en `y` exprimé en pixels,
puis ajoute `yPercent` par-dessus. Animer `yPercent` jusqu'à 0 laisse le `y` en pixels intact.

**Règle.** Dans ce cas, déclarer `y: 0` dans les deux états du `fromTo` (voir `src/scripts/motion.ts`).
Vérification : `getComputedStyle(char).transform` doit valoir `matrix(1, 0, 0, 1, 0, 0)` après l'animation.
