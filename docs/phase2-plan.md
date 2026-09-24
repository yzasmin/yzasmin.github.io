Plan de la phase 2 : six projets data réalisés, publiés sur GitHub, documentés dans le site avec un teaser. Ce fichier sert de point de reprise.

# Phase 2 : plan et état

Démarré le 22/09/2026. Mode direct (pas de branches ni de PR) : chaque projet est un dépôt git
indépendant, le site est modifié en place.

## Chemins

- Racine : `C:\Users\33769\Desktop\Offre d'emploi\Portfolio` (l'apostrophe impose de toujours citer les chemins dans le shell).
- Code des projets : `Portfolio/projets-data/<slug>/`, un dépôt git par projet, poussé en public sur `github.com/yzasmin/<depot>`.
- Site : `Portfolio/site/`. Fiches : `src/content/projets/NN-<slug>.md`. Images : `public/images/projets/<slug>/`. Teasers : `public/video/projets/<slug>.mp4`.
- Gabarit vidéo : `Portfolio/video/teaser-template/` (8 s d'origine, étendu à 15-20 s en phase 2, voir l'étape T).
- Entretiens : `site/docs/entretien/<slug>.md`. Leçons : `site/docs/lessons/` (un fichier par leçon, résumé d'une ligne en tête).

## Conventions communes à tous les projets

1. **Chiffres** : toute valeur d'une fiche ou d'un README vient d'une exécution réelle, enregistrée dans le dépôt
   (`results/*.json`, `results/*.csv`, logs ou sorties de notebook). Projets 1 et 2 : uniquement les fichiers de
   `Portfolio/inputs/`. Aucun arrondi à la hausse. Un résultat décevant est publié et expliqué dans les limites.
2. **Environnement** : Python via uv (`pyproject.toml` + `uv.lock`, commande `uv` ou `python -m uv`), R via renv (`renv.lock`).
   Données volumineuses téléchargées par script, jamais commitées.
3. **README du dépôt** (français) : Problème, Résultats (tableau + graphique), Reproduire (commandes copiables depuis un clone
   vierge), Structure, Données et licence, Limites, Crédits. Pas de tiret cadratin (caractère U+2014) nulle part.
4. **Secrets** : `.env` dans `.gitignore`, `.env.example` commité. Scanner le dépôt avant le premier push.
5. **Fiche** : gabarit `docs/gabarit-projet.md`. Garder `slug`, `ordre`, `famille`, `motif`. `statut: publie` seulement quand tout
   est vérifié. Six sections, tableau des choix techniques de 4 lignes minimum, métriques en tuiles (3 à 4), liens GitHub,
   démo et notebook quand ils existent, `teaser: 'video/projets/<slug>.mp4'`. Images référencées en `/images/projets/<slug>/x.png`.
6. **Teaser** : chaque projet fournit `projets-data/<slug>/teaser/` avec `variables.json` (valeurs réelles) et un ou deux
   graphiques en thème sombre (fond `#0D0F12`, texte `#ECE8DF`, accent de la famille : science `#FF5C8F`,
   analyse `#3FD1BE`, engineering `#F2B84B`), PNG 1600x900. Le rendu vidéo est centralisé (étape T) pour ne lancer
   qu'un Chrome à la fois.
7. **Ressources du poste** : 8 Go de RAM, souvent moins de 1 Go libre, GPU MX350 2 Go. Traiter les données par morceaux,
   préférer DuckDB ou polars en mode paresseux, pas de gros modèles locaux, un seul processus lourd à la fois.
8. **Build du site** : centralisé (`npm run build` dans `site/`), pour éviter des builds concurrents.

## Projets

| #   | Slug                | Dépôt GitHub                                                                          | Dépend de                 | État                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| --- | ------------------- | ------------------------------------------------------------------------------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1   | projet-fin-etudes   | [emotic-emotions-contexte](https://github.com/yzasmin/emotic-emotions-contexte)       | inputs/poster             | terminé et vérifié (22/09) : clone vierge reproduit en 4 min 47 (12 tests), teaser 17 s rendu, build du site à 8 pages, vérification indépendante conforme                                                                                                                                                                                                                                                                                                                                                                                                    |
| 2   | challenge-kaggle    | [geolifeclef-2025-multimodal](https://github.com/yzasmin/geolifeclef-2025-multimodal) | inputs/Kaggle             | terminé et vérifié (22/09) : clone vierge reproduit (6 tests, 2 min 20), teaser 17 s rendu, vérification indépendante conforme                                                                                                                                                                                                                                                                                                                                                                                                                                |
| 3   | data-analyst        | [dvf-herault-immobilier](https://github.com/yzasmin/dvf-herault-immobilier)           | DVF (data.gouv.fr)        | fait : dépôt poussé, Pages en ligne (démo stlite et notebook vérifiés dans le navigateur), fiche publiée, entretien, 5 leçons p3, `teaser/` prêt, clone vierge rejoué (6 tests, indicateurs identiques) ; Power BI installé le 23/09 : projet `.pbip` (modèle TMDL + rapport) ouvert et actualisé dans Power BI Desktop 2.157.1354.0, 129 comparaisons DAX contre SQL sans écart, `.pbix` non versionné (CGU DVF)                                                                                                                                             |
| 4   | data-engineer       | [velomagg-streaming-pipeline](https://github.com/yzasmin/velomagg-streaming-pipeline) | flux GBFS, Docker         | fait : Docker local inutilisable (socket périmé), pile exécutée en CI ([run 35797505264](https://github.com/yzasmin/velomagg-streaming-pipeline/actions/runs/35797505264)) avec rejeu de l'archive réelle 5,14 h, 16 848 relevés insérés, 42 tests dbt, latence p95 41,0 s ; fiche publiée, entretien, 3 leçons p4, teaser/ prêt ; capture Grafana impossible (pas de navigateur en CI), panneaux reproduits en image                                                                                                                                         |
| 5   | agent-ia-llm        | [re2020-assistant-rag](https://github.com/yzasmin/re2020-assistant-rag)               | documents RE2020, Ollama  | EN PAUSE à la demande de Yasmina le 23/09, pour réflexion sur l'approche. Acquis : dépôt poussé, récupération mesurée (hybride rappel@10 0,875, MRR 0,541, nDCG@10 0,614), teaser rendu, fiche en-cours honnête. Génération locale (Qwen2.5 1,5 Md Q4_K_M) dégénérée en configuration agent outillé : 3,1 % de réponses dans le périmètre, 32 réponses sur 37 sans citation, latence médiane 62,5 s. Configuration sans appel d'outils commencée, non terminée. Reprise : trancher modèle local contre modèle hébergé, et agent outillé contre chaîne directe |
| 6   | analyse-statistique | [qualite-air-occitanie](https://github.com/yzasmin/qualite-air-occitanie)             | Atmo Occitanie, R, Quarto | terminé et vérifié (23/09) : dépôt public poussé (branche master), Pages en ligne (https://yzasmin.github.io/qualite-air-occitanie/), teaser 17 s rendu, vérification indépendante conforme ; volume conservé corrigé à 119 Mo dans le rapport (mesuré)                                                                                                                                                                                                                                                                                                       |
| 7   | radar-entreprises   | [radar-entreprises-herault](https://github.com/yzasmin/radar-entreprises-herault)     | BODACC, SIRENE, AWS       | fait (24/09) : comble les trous « ni orchestration, ni cloud, ni entrepôt » de l'audit. Graphe Airflow 2.10.5 (8 tâches, bronze/silver/gold en Parquet partitionné), **exécuté sur le vrai S3** `amzn-s3-seau` (eu-north-1) : 12 parutions rejouées, 11 publiées, 1 refusée par les contrôles (le 16/09, une seule annonce publiée par le BODACC), 1 371 s au total, 17 592 164 octets écrits, coût mesuré 0,0015 $/mois. Orchestration prouvée en CI sur LocalStack (dépôt public, aucune clé réelle). 15 contrôles de qualité dont 13 bloquants, 135 tests, Terraform validé mais non appliqué, Athena non exécuté (assumé). Fiche publiée, entretien, 7 leçons p7, `teaser/` prêt   |
| T   | teasers             | (site)                                                                                | 1 à 6 livrent `teaser/`   | terminé : gabarit `video/teaser-projet` (17 s, check sans erreur) et les six teasers rendus dans `site/public/video/projets/` ; **teaser du projet 7 à rendre** (`teaser/variables.json` et figure 1600x900 prêts)                                                                                                                                                                                                                                                                                                                                                                                                                                 |
| V   | vérification        | (tous)                                                                                | chaque projet terminé     | projets 1, 2, 3, 4 et 6 conformes (pour le 3 : trois défauts trouvés puis corrigés et recontrôlés en ligne, commit 1a32883) ; projet 5 à lancer                                                                                                                                                                                                                                                                                                                                                                                                               |

Les projets 1 à 6 sont indépendants et tournent en parallèle (un sous-agent chacun). T et V suivent chaque projet.

### Étapes communes à chaque projet (P1 à P6)

1. Vérifier l'accès et la licence de la source (skill deep-research), noter le résultat dans le README.
2. Construire le code dans `projets-data/<slug>/`, l'exécuter de bout en bout, enregistrer les sorties dans `results/`.
3. Créer le dépôt : `gh repo create yzasmin/<depot> --public --source . --push`.
4. Écrire la fiche, `docs/entretien/<slug>.md` (8 questions et réponses) et les leçons nouvelles.
5. Copier 2 à 4 graphiques dans `site/public/images/projets/<slug>/`, préparer `teaser/`.
6. Critère de sortie : clone vierge qui s'exécute, chaque chiffre de la fiche retrouvé dans `results/` ou dans les inputs.

### Particularités

- **P1 EMOTIC** : notebooks Colab sans sorties ; les chiffres viennent du poster. Co-autrice : Malala Ravalisaona.
  Nettoyage : chemins Drive en configuration, pas de résultat inventé. Les données EMOTIC ne se redistribuent pas.
  Test de fumée : les architectures sur tenseurs aléatoires, sur CPU.
- **P2 GeoLifeCLEF** : dépôt d'équipe sous licence MIT (Guilhem), cinq membres à créditer. Retirer environnements,
  wheels, poids, caches et soumissions. Le classement final montre la ligne « Baseline participant random » (0,21495)
  au-dessus de toutes les équipes (Groupe 1 : 0,20227) : à expliquer tel quel.
- **P3 DVF** : démo Streamlit publique sans compte tiers, via stlite sur GitHub Pages. Mesures DAX documentées ;
  si Power BI Desktop est absent, le dire, et recalculer chaque mesure en SQL pour les chiffres publiés.
  23/09 : Yasmina a installé Power BI Desktop (Microsoft Store, 2.157.1354.0). Livrable attendu : projet .pbip
  (modèle TMDL et mesures DAX) réellement ouvert dans Power BI, capture du rapport, concordance DAX contre SQL.
- **P4 Vélomagg** : Docker Compose (Redpanda, producteur, consommateur, PostgreSQL, dbt, Grafana). Collecter réellement
  pendant plusieurs heures avant de publier des chiffres. Si le flux est inaccessible, flux GBFS public le plus proche, noté.
- **P5 RE2020** : 23/09, décision de Yasmina : modèle ouvert en local via Ollama plutôt que l'API Anthropic.
  Plus de clé à fournir. Modèle petit (3 milliards de paramètres, quantifié) adapté aux 8 Go de RAM, latence
  réelle publiée telle quelle, juge de fidélité local (limite à assumer et à écrire dans la fiche).
- **P6 Atmo** : R, Quarto et renv installés via winget le 22/09/2026 ; rapport HTML publié sur GitHub Pages.

### Étape T : teasers

Fait : `Portfolio/video/teaser-projet/` (copie étendue du gabarit, 17 s, scène 2 avec la figure réelle, la seconde métrique
et l'adresse du dépôt ; `npx hyperframes check` sans erreur). Rendu d'un projet, dans Git Bash :
`cd "Portfolio/video/teaser-projet" && ./render.sh <slug>` (lit `projets-data/<slug>/teaser/`, rend en `--workers 1`
vers `site/public/video/projets/<slug>.mp4`, environ 1 min 10 pour 17 s, puis contrôle ffprobe).

### Étape V : vérification

Pour chaque projet, un sous-agent au contexte neuf : clone vierge, exécution, correspondance de chaque chiffre de la fiche
avec une sortie, conformité au gabarit, `npm run build` du site.

## Journal

- 22/09/2026 : lecture des entrées, plan écrit, installation de uv, R et Quarto lancée.
