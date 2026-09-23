import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

gsap.registerPlugin(ScrollTrigger);

const EASE = 'power3.out';
const onEnter = (trigger: Element) => ({ trigger, start: 'top 88%', once: true });

// Tout le contenu est visible sans JavaScript ; les animations ne s'exécutent
// que si l'utilisateur n'a pas demandé à réduire les mouvements.
gsap.matchMedia().add('(prefers-reduced-motion: no-preference)', () => {
  gsap.fromTo(
    '[data-hero-char]',
    // y: 0 annule la translation CSS initiale, que GSAP convertirait sinon en pixels.
    { y: 0, yPercent: 105 },
    { y: 0, yPercent: 0, duration: 1, ease: 'power4.out', stagger: 0.035, delay: 0.05 },
  );
  gsap.from('[data-hero-fade]', { y: 18, autoAlpha: 0, duration: 0.8, ease: EASE, stagger: 0.08, delay: 0.35 });

  gsap.utils.toArray<HTMLElement>('[data-reveal]').forEach((element) => {
    gsap.from(element, { y: 28, autoAlpha: 0, duration: 0.8, ease: EASE, scrollTrigger: onEnter(element) });
  });

  gsap.utils.toArray<HTMLElement>('[data-draw]').forEach((element) => {
    gsap.from(element, {
      scaleX: 0,
      transformOrigin: 'left center',
      duration: 1.1,
      ease: 'power3.inOut',
      scrollTrigger: onEnter(element),
    });
  });

  gsap.utils.toArray<HTMLElement>('[data-bar]').forEach((element, index) => {
    gsap.from(element, {
      scaleX: 0,
      duration: 1.1,
      ease: 'power3.inOut',
      delay: (index % 4) * 0.12,
      scrollTrigger: onEnter(element),
    });
  });

  // Pas de décompte animé sur les chiffres du parcours : pendant l'animation, la page
  // affichait « 0 ans d'alternance », « 1 ans de freelance », « ~7 rapports », « 0re place ».
  // Un chiffre faux, même une seconde, n'a pas sa place ici. Le bloc garde son apparition
  // en fondu, la valeur reste celle rendue par le serveur.
});
