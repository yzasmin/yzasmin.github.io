// Source de vérité du contenu. Dates et intitulés repris du CV 2026.

export type Family = 'science' | 'analyse' | 'engineering' | 'web';

export const families: Record<Family, { label: string; color: string }> = {
  science: { label: 'Data Science et IA', color: 'var(--signal)' },
  analyse: { label: 'Analyse et BI', color: 'var(--teal)' },
  engineering: { label: 'Data Engineering', color: 'var(--amber)' },
  web: { label: 'Web et produit', color: 'var(--indigo)' },
};

export const person = {
  firstName: 'Yasmina',
  lastName: 'Saoud',
  roles: [
    { label: 'Data Scientist', family: 'science' as Family },
    { label: 'Data Analyst', family: 'analyse' as Family },
    { label: 'Data Engineer', family: 'engineering' as Family },
  ],
  location: 'Montpellier / Béziers',
  availability: 'Octobre 2026',
  email: 'saoudyasmina.ys@gmail.com',
  linkedin: 'https://fr.linkedin.com/in/yasmina-saoud-ysstudiodesign',
  github: 'https://github.com/yzasmin',
  languages: 'Français natif, anglais C1, arabe C2, espagnol B2',
  pitch:
    "Je fiabilise les chiffres sur lesquels une direction décide, j'automatise les rapports encore faits à la main, et je rends chaque analyse reproductible et défendable.",
};

// Ce que cherche Yasmina, affiché dès le premier écran : c'est la première question d'un service RH.
export const search = {
  contract: 'CDI ou CDD',
  mobility: 'Montpellier, Béziers et alentours, mobile dans la région',
  availability: 'Octobre 2026',
  sentence:
    "Je recherche un CDI ou un CDD de Data Analyst, Data Engineer ou Data Scientist, à Montpellier, Béziers et alentours, mobile dans la région. Disponible à partir d'octobre 2026.",
};

// Ce que l'entreprise gagne, avec la preuve chiffrée à côté. Pas une liste d'outils.
export const valueProps = [
  {
    title: 'Des chiffres sur lesquels une direction peut décider',
    text: 'Je vérifie mes chiffres avant de les publier : mes vingt mesures Power BI rejouées une par une en SQL, 129 comparaisons, écart maximal de 4,8e-14. Et chez Angelotti, une migration ERP fiabilisée, environ 1 440 tiers rapprochés sans perte de donnée critique.',
  },
  {
    title: 'Des rapports qui ne se refont plus à la main',
    text: "Une trentaine de rapports et tableaux de bord Power BI pour cinq services métier, alimentés par des pipelines ETL sous SQL Server et des connecteurs vers l'ERP, des API REST et des bases SQL. Le temps passé à recopier des extractions repart vers l'analyse.",
  },
  {
    title: 'Des analyses reproductibles et défendables',
    text: "Chaque chiffre affiché existe dans un fichier du dépôt, chaque règle de nettoyage est comptée, chaque projet publie ses limites. Les six projets de ce portfolio suivent ce protocole. C'est ce qui permet de défendre un résultat en réunion, pas seulement de le montrer.",
  },
];

export const aboutClosing =
  'Le Master MIASHS a donné le socle mathématique et statistique ; le deep learning, le NLP et les LLM sont venus avec les projets. Cinq ans à diriger ma propre activité ont appris le reste : écouter un besoin, le cadrer, le chiffrer, livrer, former.';

export const stats = [
  { value: 2, prefix: '', suffix: ' ans', label: "d'alternance data, Groupe Angelotti (filiale Nexity)" },
  { value: 5, prefix: '', suffix: ' ans', label: 'à diriger ma propre activité : cadrer, chiffrer, livrer, former' },
  { value: 30, prefix: '~', suffix: '', label: 'rapports et tableaux de bord Power BI pour 5 services métier' },
  { value: 1, prefix: '', suffix: 're', label: 'place au Deep Learning Challenge MIASHS 2026' },
];

export const softSkills = [
  'Autonomie',
  'Esprit critique',
  'Créativité',
  'Apprentissage rapide',
  'Méthode agile',
  'Passion pour la data science',
];

export const stack: { category: string; family: Family; tools: string[] }[] = [
  { category: 'Langages', family: 'science', tools: ['Python', 'R', 'SQL', 'JavaScript'] },
  {
    category: 'Data science et ML',
    family: 'science',
    tools: ['pandas', 'scikit-learn', 'SHAP', 'statsmodels'],
  },
  {
    category: 'Deep learning',
    family: 'science',
    tools: ['PyTorch', 'TensorFlow', 'Modèles de diffusion', 'PyTorch Lightning', 'Transformers'],
  },
  {
    category: 'IA générative et LLM',
    family: 'science',
    tools: ['NLP', 'RAG', 'LLM', 'Prompt engineering', "Workflows d'agents IA", 'Claude Code', 'Bases vectorielles'],
  },
  {
    category: 'Data engineering',
    family: 'engineering',
    tools: ['ETL / ELT', 'Spark', 'Kafka', 'Redpanda', 'Flink', 'Big Data', 'AWS'],
  },
  {
    category: 'Bases de données',
    family: 'engineering',
    tools: ['SQL Server', 'PostgreSQL', 'NoSQL', 'Bases vectorielles'],
  },
  {
    category: 'BI et dataviz',
    family: 'analyse',
    tools: [
      'Power BI',
      'DAX',
      'Power Query',
      'Modélisation sémantique',
      'Tableau',
      'Streamlit',
      'Matplotlib',
      'Seaborn',
    ],
  },
  {
    category: 'Statistiques',
    family: 'analyse',
    tools: ['Statistiques inférentielles', 'Probabilités', 'R'],
  },
  { category: 'Géospatial', family: 'analyse', tools: ['QGIS'] },
  { category: 'API et tests', family: 'engineering', tools: ['REST', 'Postman', 'Newman'] },
  { category: 'Outils', family: 'engineering', tools: ['Git', 'GitHub', 'Docker'] },
  {
    category: 'Web',
    family: 'web',
    tools: ['HTML', 'CSS', 'JavaScript', 'UX/UI', 'Figma', 'SEO', 'Hébergement', 'Bases en cybersécurité'],
  },
];

// Barres du Gantt en années décimales (2024.75 = octobre 2024). Les libellés affichent les dates du CV.
export const timeline = {
  start: 2019,
  end: 2027,
  rows: [
    {
      title: 'Développeuse web, activité indépendante',
      org: 'Activité indépendante, Montpellier',
      dates: '2019 à 2024',
      from: 2019,
      to: 2024.75,
      family: 'web' as Family,
      points: [
        'Création et développement de mon activité : prospection, chiffrage, livraison, en autonomie complète.',
        'Solutions web sur mesure pour des TPE et PME, du cadrage du besoin à la formation des utilisateurs.',
      ],
    },
    {
      title: 'Licence MIASHS, mention Assez Bien',
      org: 'Université Paul Valéry Montpellier 3',
      dates: '2020 à 2024',
      from: 2020.7,
      to: 2024.5,
      family: 'analyse' as Family,
      points: [],
    },
    {
      title: 'Master MIASHS, mention Bien',
      org: 'Université Paul Valéry Montpellier 3',
      dates: '2024 à 2026',
      from: 2024.7,
      to: 2026.6,
      family: 'science' as Family,
      points: [],
    },
    {
      title: 'Data Analyst / Data Engineer, alternance',
      org: 'Groupe Angelotti (filiale Nexity), Béziers',
      dates: '10/2024 à 10/2026',
      from: 2024.75,
      to: 2026.75,
      family: 'engineering' as Family,
      points: [
        'Une trentaine de rapports et tableaux de bord Power BI pour 5 services : commercial, juridique, contrôle de gestion, directions financière et opérationnelle.',
        "Pipelines ETL/ELT sous SQL Server et connecteurs d'ingestion depuis un ERP, des API REST et des bases SQL.",
        'Migration ERP fiabilisée : environ 1 440 tiers et 300 opérations rapprochés, zéro perte de donnée critique.',
        "Outil Python d'aide à la décision : pipeline de features et modèles de prédiction du risque de marge.",
      ],
    },
  ],
  milestones: [{ label: '1re place, Deep Learning Challenge MIASHS', date: 'Avril 2026', at: 2026.29 }],
  certifications: [{ label: 'Generative AI with Diffusion Models', issuer: 'NVIDIA', date: '2026' }],
};

export const cvFiles = [
  { label: 'Data Scientist / Data Analyst', file: 'cv/CV-Yasmina-Saoud-Data-Scientist.pdf' },
  { label: 'Data Analyst BI', file: 'cv/CV-Yasmina-Saoud-Data-Analyst.pdf' },
  { label: 'Data Engineer', file: 'cv/CV-Yasmina-Saoud-Data-Engineer.pdf' },
];
