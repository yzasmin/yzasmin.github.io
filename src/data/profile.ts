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
  location: 'Occitanie',
  availability: 'Octobre 2026',
  email: 'saoudyasmina.ys@gmail.com',
  linkedin: 'https://fr.linkedin.com/in/yasmina-saoud-ysstudiodesign',
  github: 'https://github.com/yzasmin',
  languages: 'Français natif, anglais C1, arabe C2, espagnol B2',
  pitch:
    "Je fiabilise les chiffres sur lesquels une direction décide, et j'automatise les rapports qu'on refait encore à la main. Chaque analyse que je publie peut être rejouée par quelqu'un d'autre.",
};

// Ce que cherche Yasmina, affiché dès le premier écran : c'est la première question d'un service RH.
export const search = {
  contract: 'Contrat salarié',
  mobility: 'Occitanie, télétravail partiel ou complet possible',
  availability: 'Octobre 2026',
  sentence:
    "Je cherche un poste salarié de Data Analyst, Data Engineer ou Data Scientist en Occitanie, avec télétravail partiel ou complet. Disponible à partir d'octobre 2026.",
};

// Ce que l'entreprise gagne, avec la preuve chiffrée à côté. Pas une liste d'outils.
export const valueProps = [
  {
    title: 'Des chiffres vérifiés avant publication',
    text: "Mes vingt mesures Power BI, je les ai rejouées une par une en SQL avant de les montrer : 129 comparaisons, écart maximal de 4,8e-14. Chez Angelotti, j'ai rapproché environ 1 440 tiers pendant une migration ERP, sans perte de donnée critique.",
  },
  {
    title: 'Des rapports qui ne se refont plus à la main',
    text: "Une centaine de rapports et tableaux de bord Power BI pour cinq services métier, alimentés par des pipelines ETL sous SQL Server et des connecteurs vers l'ERP, des API REST et des bases SQL. Le temps passé à recopier des extractions repart vers l'analyse.",
  },
  {
    title: "Des analyses qu'on peut rejouer",
    text: "Chaque chiffre affiché vient d'un fichier du dépôt, les règles de nettoyage sont comptées et les limites sont écrites noir sur blanc. Les projets de ce portfolio suivent tous ce protocole. C'est ce qui permet de tenir un résultat en réunion quand quelqu'un le conteste.",
  },
];

export const aboutClosing =
  "Le socle est mathématique et statistique. Le deep learning, le NLP et les LLM sont venus avec les projets, et cinq ans à mon compte ont appris le reste : écouter un besoin, le chiffrer, livrer, puis former les gens qui s'en servent.";

export const stats = [
  { value: 2, prefix: '', suffix: ' ans', label: 'en data chez Groupe Angelotti, filiale de Nexity' },
  { value: 5, prefix: '', suffix: ' ans', label: 'à diriger ma propre activité : cadrer, chiffrer, livrer, former' },
  { value: 100, prefix: '~', suffix: '', label: 'rapports et tableaux de bord Power BI pour 5 services métier' },
  { value: 1, prefix: '', suffix: 're', label: 'des 4 équipes au challenge Kaggle de deep learning 2026' },
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
      title: 'Data Analyst / Data Engineer',
      org: 'Groupe Angelotti (filiale Nexity), Béziers',
      dates: '10/2024 à 10/2026',
      from: 2024.75,
      to: 2026.75,
      family: 'engineering' as Family,
      points: [
        'Une centaine de rapports et tableaux de bord Power BI pour 5 services : commercial, juridique, contrôle de gestion, directions financière et opérationnelle.',
        "Pipelines ETL/ELT sous SQL Server et connecteurs d'ingestion depuis un ERP, des API REST et des bases SQL.",
        'Migration ERP fiabilisée : environ 1 440 tiers et 300 opérations rapprochés, zéro perte de donnée critique.',
        "Outil Python d'aide à la décision : pipeline de features et modèles de prédiction du risque de marge.",
      ],
    },
  ],
  milestones: [{ label: '1re des 4 équipes, challenge Kaggle de deep learning', date: 'Avril 2026', at: 2026.29 }],
  certifications: [{ label: 'Generative AI with Diffusion Models', issuer: 'NVIDIA', date: '2026' }],
};

// Diplôme affiché hors du diagramme : le site met en avant l'expérience et le niveau, pas le
// calendrier des études (conseil d'un recruteur, octobre 2026). Le détail se donne en entretien.
export const education = [
  { title: 'Master MIASHS, mathématiques et informatique appliquées', org: 'Université Paul Valéry Montpellier 3' },
];

export const cvFiles = [
  { label: 'Data Scientist / Data Analyst', file: 'cv/CV-Yasmina-Saoud-Data-Scientist.pdf' },
  { label: 'Data Analyst BI', file: 'cv/CV-Yasmina-Saoud-Data-Analyst.pdf' },
  { label: 'Data Engineer', file: 'cv/CV-Yasmina-Saoud-Data-Engineer.pdf' },
];
