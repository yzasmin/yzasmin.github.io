---
slug: data-analyst
titre: "Prix de l'immobilier dans l'Hérault"
ordre: 3
categorie: 'Analyse et BI'
famille: analyse
resume: "Cinq ans de ventes immobilières de l'Hérault (Demandes de valeurs foncières, 412 496 lignes brutes) transformées en indicateurs publiables : nettoyage chiffré sous DuckDB, prix médians au m² avec intervalles de confiance, rapport Power BI dont les 20 mesures DAX sont contrôlées contre le SQL, et une démo Streamlit qui tourne dans le navigateur."
statut: publie
motif: bars
stack: ['Python', 'DuckDB', 'SQL', 'pandas', 'Power BI', 'DAX', 'TMDL', 'Streamlit', 'stlite', 'uv']
liens:
  github: 'https://github.com/yzasmin/dvf-herault-immobilier'
  demo: 'https://yzasmin.github.io/dvf-herault-immobilier/demo/'
  notebook: 'https://yzasmin.github.io/dvf-herault-immobilier/notebook.html'
teaser: 'video/projets/data-analyst.mp4'
metriques:
  - { label: 'Ventes retenues sur 178 905 mutations', valeur: '105 463' }
  - { label: 'Prix médian au m², appartements, 2025', valeur: '3 483 €' }
  - { label: 'Évolution 2021-2025, appartements (IC 95 % : +8,8 à +11,6 %)', valeur: '+10,2 %' }
  - { label: 'Ventes en 2024 par rapport à 2021', valeur: '-30,9 %' }
---

## Contexte et problème

« Est-ce le bon moment pour acheter, et à quel prix dans ma commune ? » Un acheteur, une agence et une collectivité
posent la même question avec des enjeux différents : arbitrer un achat, fixer un prix de mise en vente, suivre la
tension du marché local. La réponse existe en open data : les Demandes de valeurs foncières (DVF) contiennent toutes
les ventes immobilières enregistrées par la DGFiP.

Elles sont inexploitables telles quelles. Une vente s'étale sur plusieurs lignes (une par local et par nature de
culture du terrain) et la valeur foncière est répétée sur chacune : la sommer compte le prix deux, trois ou dix
fois. Les ventes de garages, de terrains nus et de lots de dix appartements se mélangent aux ventes de logements.
Des prix au m² à 50 euros côtoient des prix à 50 000 euros.

Ce projet fait le trajet complet sur le département de l'Hérault, de 2021 à 2025 : nettoyer en justifiant et en
comptant chaque retrait, produire des indicateurs stables, et livrer le résultat sous deux formes utilisables,
un modèle Power BI documenté et une démo publique consultable sans rien installer.

## Données

- **Source** : DVF géolocalisées d'Etalab, fichiers départementaux annuels `34.csv.gz`, téléchargés par script et
  jamais commités. Millésimes disponibles au 22/09/2026 : **2021 à 2025** (le serveur conserve cinq années
  glissantes), fichiers publiés le 18/05/2026. Les empreintes SHA-256 des cinq fichiers sont dans
  `results/sources.json`.
- **Volume** : 412 496 lignes brutes, 178 905 mutations, 342 communes ; les ventes retenues vont du 04/01/2021 au
  31/12/2025 (`results/resume.json`).
- **Licence et contraintes** : Licence Ouverte 2.0, assortie des conditions générales d'utilisation DVF
  (art. R. 112 A-3 du Livre des procédures fiscales) : pas de réidentification des personnes, pas d'indexation des
  données par les moteurs de recherche. Conséquences concrètes : aucune vente individuelle n'est publiée, tout est
  agrégé avec un seuil de 30 ventes par commune, et les pages en ligne portent une balise `noindex`.
- **Nettoyage, neuf règles chiffrées** (journal complet dans `results/journal_nettoyage.csv`) : doublons exacts
  (20 254 lignes), nature « Vente » uniquement (16 767 mutations retirées, dont 14 343 ventes en l'état futur
  d'achèvement), valeur foncière positive (156), aucun local commercial dans la mutation (8 414), au moins un
  logement (37 962, la règle la plus lourde : terrains, parkings et caves vendus seuls), un seul logement
  (5 229, faute de pouvoir ventiler un prix global), surface bâtie entre 9 et 1 000 m² (19), puis prix au m²
  aberrants (4 891). Il reste **105 463 ventes, 58,9 % des mutations**.
- **Règle des aberrants** : bornes de Tukey (Q1 - 1,5 IQR ; Q3 + 1,5 IQR) appliquées au **logarithme** du prix au
  m², par type de bien et par année. Sans logarithme, la borne basse serait négative et ne retirerait rien ; avec,
  90 % des retraits (4 405 sur 4 891) se font par le bas, là où se trouvent les cessions entre proches, les parts
  indivises et les erreurs de surface.
- **Biais assumés** : pas de VEFA donc pas de marché du neuf, pas de caractéristiques qualitatives (étage, état,
  DPE, vue), terrain inclus dans le prix des maisons, prix nominaux.

## Approche

1. **Téléchargement** scripté (`scripts/telecharger.py`), qui lit l'index du serveur pour découvrir les millésimes
   et écrit un manifeste avec taille et empreinte.
2. **Nettoyage et agrégation à la mutation** en SQL DuckDB, lu directement dans les `.csv.gz`, mémoire limitée à
   600 Mo. Chaque règle est un pas séparé, compté avant et après.
3. **Modèle en étoile et rapport Power BI** : `fait_ventes` (105 463 lignes), `dim_date`, `dim_commune`,
   `dim_type_bien`, exportés en Parquet, et un projet Power BI versionnable (`.pbip`) dont le modèle sémantique est
   écrit en TMDL et les 20 mesures DAX générées depuis `powerbi/mesures.dax`, ouvert et actualisé dans Power BI
   Desktop 2.157.1354.0.
4. **Indicateurs** calculés sur ce modèle, avec les mêmes définitions que les mesures DAX : médianes par année,
   type et commune, volumes, évolutions, seuil de publication à 30 ventes.
5. **Incertitude** : intervalles de confiance à 95 % de chaque médiane et de chaque évolution par bootstrap
   (2 000 rééchantillons, graine fixée), calculés par lots de 100 pour tenir dans la mémoire disponible.
6. **Livraison** : notebook exécuté de bout en bout, quatre graphiques, démo Streamlit publiée via stlite
   (Streamlit compilé en WebAssembly) sur GitHub Pages, donc sans serveur ni compte tiers.

## Choix techniques

| Choix                                 | Plutôt que                   | Pourquoi                                                                                                                                       |
| ------------------------------------- | ---------------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------- |
| DuckDB sur les `.csv.gz`              | pandas en mémoire            | 412 496 lignes lues sans décompression sur disque, avec `memory_limit='600MB'` sur un poste où il reste souvent moins de 1 Go libre            |
| Analyse à la mutation                 | analyse ligne à ligne        | La valeur foncière est répétée sur chaque ligne d'une mutation : agréger d'abord évite de compter un prix plusieurs fois                       |
| Médiane et quartiles                  | moyenne                      | La distribution du prix au m² a une longue queue à droite : la moyenne 2025 (3 602 €/m² pour les appartements) dépasse la médiane (3 483 €/m²) |
| IQR sur le log du prix au m²          | bornes fixes en euros        | Une borne fixe vieillit mal et dépend du type de bien ; le log symétrise la distribution et rend la règle comparable d'une année à l'autre     |
| Seuil de 30 ventes par commune        | publier toutes les communes  | Une médiane sur 5 ventes n'est pas un indicateur ; le seuil laisse 25 communes sur 143 pour les appartements 2025, mais 93 % des ventes        |
| stlite sur GitHub Pages               | Streamlit Community Cloud    | Démo publique sans compte tiers ni serveur à maintenir, hébergée dans le même dépôt que le code                                                |
| Projet Power BI `.pbip` (modèle TMDL) | fichier `.pbix` binaire      | Le modèle et les mesures sont du texte : relecture, diff et génération depuis `mesures.dax`, une seule définition des mesures dans le dépôt    |
| Mesures DAX contrôlées contre le SQL  | croire le rapport sur parole | Les 20 mesures sont interrogées dans le moteur de Power BI puis comparées au même calcul DuckDB : 129 comparaisons, aucune différence          |

## Résultats et métriques

![Prix médian au m² dans l'Hérault de 2021 à 2025, appartements et maisons, avec intervalle de confiance](/images/projets/data-analyst/prix-m2-herault.png)

- **Les prix ont monté jusqu'en 2023, puis se sont arrêtés.** Prix médian au m², tous logements : 3 006,67 € en
  2021, pic à 3 355,77 € en 2023, 3 301,08 € en 2025. Sur cinq ans, +10,2 % pour les appartements
  (IC 95 % : +8,8 % à +11,6 %) et +7,5 % pour les maisons (+6,0 % à +9,1 %). Ce sont des euros courants : corrigée
  de l'inflation de ces cinq années, la hausse fondrait largement.
- **Le volume a bien plus bougé que les prix** : 24 809 ventes retenues en 2021, 17 147 en 2024 (-30,9 %), puis
  19 453 en 2025 (+13,4 %). Le marché s'est bloqué plus qu'il n'a baissé.

![Nombre de ventes retenues par année et par type de bien](/images/projets/data-analyst/volumes-annuels.png)

- **Le littoral décide des prix, pas la métropole.** En 2025, parmi les 25 communes publiables pour les
  appartements : La Grande-Motte 5 232 €/m², Palavas-les-Flots 4 895 €/m², Béziers 1 833 €/m², soit un rapport de
  2,9 entre les extrêmes. Montpellier (3 425 €/m²) n'est que 17e, dépassée par Sète (3 497 €/m²), qui était
  277 €/m² en dessous en 2021 (2 898 contre 3 175 €/m²).

![Prix médian au m² des appartements à Montpellier, Sète et Béziers de 2021 à 2025](/images/projets/data-analyst/trois-villes.png)

- **Les communes les moins chères sont celles qui ont le plus augmenté.** Corrélation de rang de Spearman entre le
  prix médian de 2021 et l'évolution 2021-2025 : -0,65 pour les appartements (22 communes, p = 0,0017 par test de
  permutation) et -0,32 pour les maisons (82 communes, p = 0,0030). Un rattrapage est plausible, mais la régression
  vers la moyenne produit le même signe : le résultat est publié avec cette réserve.
- **Le rapport Power BI affiche les mêmes chiffres.** Le projet `.pbip` ouvert dans Power BI Desktop
  2.157.1354.0 charge les 105 463 ventes et évalue ses 20 mesures DAX. Interrogées directement dans le moteur puis
  comparées au même calcul en SQL : 129 comparaisons, 15 mesures, 10 contextes de filtre, **aucune différence**
  (écart relatif maximal 4,8e-14, soit l'arrondi des flottants).

![Le rapport Power BI ouvert dans Power BI Desktop : cartes, courbe des prix, volumes et tableau par année](/images/projets/data-analyst/rapport-power-bi.png)

- **Vérification systématique** : chaque chiffre de cette fiche existe dans `results/` du dépôt
  (`resume.json`, `ind_annee_type.csv`, `ic_evolutions.csv`, `ind_commune_annee_type.csv`, `chiffres_cles.json`,
  `rattrapage_communes.json`), et six tests pytest contrôlent l'enchaînement du journal de nettoyage, le respect du
  seuil de publication et l'égalité entre les mesures recalculées et les tables d'indicateurs.

![Classement des communes de l'Hérault par prix médian au m² des appartements en 2025](/images/projets/data-analyst/communes-appartements.png)

## Limites et pistes d'amélioration

- **Le rapport Power BI tient en une page.** Les pages « Communes » et « Comparaison de villes » prévues dans
  `powerbi/MODELE.md` restent à construire. Le fichier `.pbix` n'est pas versionné : il embarquerait les 105 463
  ventes ligne à ligne dans un dépôt public indexable, ce qu'interdisent les conditions d'utilisation DVF suivies
  partout ailleurs dans ce projet ; le `.pbip` contient la définition complète, sans donnée. Enfin, Power BI
  n'accepte pas de chemin relatif vers un fichier local : le dossier des données passe par un paramètre de requête
  à adapter après un clone.
- **DVF ne décrit pas les biens** : ni état, ni étage, ni DPE. Comparer deux communes, c'est comparer des prix et
  des biens en même temps. La suite naturelle est un modèle hédonique (prix expliqué par surface, pièces, terrain,
  commune, année) pour isoler l'effet de localisation.
- **Prix nominaux** : ajouter l'indice des prix à la consommation de l'INSEE donnerait des évolutions en euros
  constants, plus honnêtes à lire.
- **59 % des mutations conservées** : le neuf (VEFA) et les ventes en bloc sont hors périmètre, alors qu'ils pèsent
  sur l'offre réelle. Les traiter demanderait une méthode de ventilation des prix par lot.
- **Périmètre DVF** : l'Alsace, la Moselle et Mayotte ne sont pas couvertes par ces fichiers (livre foncier propre) ;
  l'Hérault l'est intégralement. La publication est semestrielle, avec plusieurs mois de décalage et des mutations
  qui peuvent s'ajouter rétroactivement sur les années passées.
- **Démo** : stlite télécharge Python dans le navigateur (environ 30 Mo au premier accès). C'est le prix d'une démo
  publique sans serveur ; une version hébergée classique démarrerait plus vite mais demanderait un compte tiers.
