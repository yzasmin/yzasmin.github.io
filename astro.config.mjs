import { defineConfig } from 'astro/config';
import mdx from '@astrojs/mdx';
import sitemap from '@astrojs/sitemap';
import tailwindcss from '@tailwindcss/vite';

// Dépôt yzasmin.github.io : le site est servi à la racine.
// Pour un dépôt de projet (ex. "portfolio"), passer base à '/portfolio'.
export default defineConfig({
  site: 'https://yzasmin.github.io',
  base: '/',
  trailingSlash: 'always',
  // Ancienne adresse du projet 1, déjà partagée : elle renvoie vers la nouvelle.
  redirects: {
    '/projets/projet-fin-etudes/': '/projets/emotions-contexte-scene/',
  },
  integrations: [mdx(), sitemap()],
  vite: { plugins: [tailwindcss()] },
});
