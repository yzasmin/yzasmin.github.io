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
  integrations: [mdx(), sitemap()],
  vite: { plugins: [tailwindcss()] },
});
