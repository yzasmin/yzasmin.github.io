import { defineCollection } from 'astro:content';
import { glob } from 'astro/loaders';
import { z } from 'astro/zod';

const projets = defineCollection({
  loader: glob({ pattern: '**/*.{md,mdx}', base: './src/content/projets' }),
  schema: z.object({
    titre: z.string(),
    ordre: z.number(),
    categorie: z.string(),
    famille: z.enum(['science', 'analyse', 'engineering', 'web']),
    resume: z.string(),
    statut: z.enum(['en-cours', 'publie']),
    motif: z.enum(['poster', 'scatter', 'bars', 'pipeline', 'graph', 'histogram']),
    stack: z.array(z.string()).default([]),
    liens: z
      .object({
        github: z.url().optional(),
        demo: z.url().optional(),
        notebook: z.url().optional(),
      })
      .default({}),
    // Chemin relatif à public/, ex. "video/projets/challenge-kaggle.mp4"
    teaser: z.string().optional(),
    metriques: z.array(z.object({ label: z.string(), valeur: z.string() })).default([]),
  }),
});

export const collections = { projets };
