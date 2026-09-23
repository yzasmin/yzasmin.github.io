Huit questions probables d'un recruteur technique sur l'analyse DVF de l'Hérault (projet 3), avec des réponses courtes fondées sur le dépôt.

# Entretien : marché immobilier de l'Hérault (DVF)

Sources des chiffres : dépôt `github.com/yzasmin/dvf-herault-immobilier`, dossier `results/`
(`journal_nettoyage.csv`, `ind_annee_type.csv`, `ic_evolutions.csv`, `ind_commune_annee_type.csv`,
`mesures_dax_sql.csv`, `concordance_dax_powerbi.csv`, `chiffres_cles.json`).

## 1. Quelle est la première erreur que font les gens avec les DVF ?

Sommer `valeur_fonciere`. Une mutation s'étale sur plusieurs lignes, une par local et par nature de culture du
terrain, et la valeur foncière de la mutation entière est répétée sur chaque ligne. Une maison sur une parcelle à
deux natures de culture apparaît deux fois : sommer double son prix. Je regroupe donc d'abord à la mutation
(`sql/02_mutations.sql`), avec `MAX(valeur_fonciere)` et un dédoublonnage des locaux ; un test pytest sur des
données synthétiques vérifie exactement ce cas.

## 2. Pourquoi ne garder que les ventes d'un seul logement ?

Parce que le prix au m² n'a pas de sens autrement. La valeur foncière est globale : si la mutation contient deux
appartements, ou un appartement et un local commercial, rien ne permet de ventiler le prix entre eux. Je retire donc
les mutations sans logement (37 962, surtout des terrains, parkings et caves vendus seuls), celles avec un local
d'activité (8 414) et celles à plusieurs logements (5 229). Il reste 105 463 ventes, 58,9 % des mutations. Les
dépendances rattachées à un logement (garage, cave) restent incluses dans le prix, et je le dis dans les limites.

## 3. Comment avez-vous traité les valeurs aberrantes, et pourquoi ce choix ?

Bornes de Tukey, Q1 - 1,5 IQR et Q3 + 1,5 IQR, appliquées au logarithme du prix au m², séparément par type de bien
et par année. Le log est important : la distribution est très dissymétrique, et sur l'échelle brute la borne basse
tombe dans les négatifs, donc ne retire rien. Résultat : 4 891 ventes retirées, dont 4 405 par le bas (90 %) :
cessions entre proches, parts indivises, viagers, erreurs de surface. Les bornes obtenues sont enregistrées
(`results/bornes_aberrants.csv`) : pour les appartements 2025, 1 082 à 10 095 €/m².

## 4. Pourquoi la médiane et pas la moyenne ?

Parce que la queue à droite tire la moyenne. Sur les appartements 2025 de l'Hérault, la moyenne est de 3 602 €/m²
et la médiane de 3 483 €/m². J'affiche les deux dans le notebook et je publie la médiane, avec les quartiles pour
la dispersion. J'ajoute un intervalle de confiance à 95 % de la médiane par bootstrap (2 000 rééchantillons, graine
fixée) : c'est ce qui permet de dire que la hausse des appartements 2021-2025, +10,2 %, est comprise entre +8,8 %
et +11,6 %, alors que pour les maisons de Montpellier l'intervalle contient 0, donc je ne conclus pas.

## 5. Pourquoi un seuil de 30 ventes par commune, et qu'est-ce que ça coûte ?

Une médiane calculée sur 5 ventes bouge d'une vente à l'autre et peut, en plus, rendre une transaction
identifiable, ce que les conditions d'utilisation DVF interdisent. Le seuil de 30 ventes par commune, année et type
laisse 25 communes sur 143 pour les appartements 2025 et 83 sur 319 pour les maisons. C'est beaucoup de communes
écartées, mais elles pèsent peu : les communes publiées couvrent 93 % des ventes d'appartements et 74 % des ventes
de maisons (`results/couverture_communes.csv`). Dans la démo, les communes sous le seuil affichent
« non publié », pas une case vide.

## 6. Quels sont les résultats, et y a-t-il un résultat qui vous a surprise ?

Trois : la hausse s'arrête en 2023 (pic à 3 355,77 €/m² tous logements, 3 301,08 € en 2025) ; le volume a beaucoup
plus bougé que les prix (-30,9 % de ventes entre 2021 et 2024, +13,4 % en 2025) ; le littoral domine le classement.
La surprise, c'est Montpellier au 17e rang des 25 communes publiables pour les appartements 2025, à 3 425 €/m²,
derrière Sète (3 497 €/m²) qui était 277 €/m² en dessous en 2021. J'ai aussi mesuré une corrélation négative entre
le prix de 2021 et l'évolution : les communes les moins chères ont le plus augmenté.

Sur ce dernier point, la première version de la preuve ne tenait pas et je l'ai refaite. Je corrélais le prix de
2021 avec `prix_2025 / prix_2021 - 1`, avec une p-valeur de permutation à 0,0017. Le problème : le prix de 2021 est
au numérateur de la première variable et au dénominateur de la seconde, donc le seul bruit d'échantillonnage sur la
médiane de 2021 produit une corrélation négative, même sans aucun rattrapage. Et permuter ne répond pas à
l'objection, puisque la permutation détruit précisément le couplage qu'il faudrait tester.

Le test que je publie maintenant sépare aléatoirement les ventes de 2021 de chaque commune en deux moitiés :
l'abscisse est estimée sur la première, le dénominateur de l'évolution sur la seconde. Les deux bruits deviennent
indépendants et le couplage disparaît. Sur 200 tirages, la corrélation de rang médiane est de -0,61 [-0,71 ; -0,47]
pour les appartements et -0,25 [-0,38 ; -0,13] pour les maisons, négative sur les 200 tirages. La conclusion n'a pas
changé, sa justification oui, et c'est maintenant une preuve que je peux défendre.

## 7. Vous parlez de Power BI, mais il n'y a pas de fichier .pbix. Pourquoi ?

Le dépôt contient un projet Power BI au format `.pbip` : le modèle sémantique est écrit en TMDL, donc en texte
lisible et diffable, et les 20 mesures DAX sont générées depuis `powerbi/mesures.dax` par
`scripts/construire_pbip.py`, ce qui évite d'avoir deux définitions des mesures. Je l'ai ouvert dans Power BI
Desktop 2.157.1354.0 : le modèle se charge, l'actualisation importe les 105 463 ventes, le rapport s'affiche
(capture dans `powerbi/capture-rapport.png`).

Le `.pbix` n'est pas versionné, et ce n'est pas une question de taille : il embarquerait les ventes ligne à ligne
dans un dépôt public indexable, alors que les conditions d'utilisation DVF interdisent l'indexation des données
par les moteurs de recherche. C'est la règle que j'applique partout dans ce projet : seuls des agrégats sortent.
Le `.pbip` contient la définition complète sans donnée, et un `.pbix` se reconstruit en local en une minute.

**Et pour vérifier que ces mesures calculent bien ce que j'annonce**, je ne me fie pas à l'écran.
Power BI Desktop expose un moteur Analysis Services local : `scripts/executer_dax.ps1`
s'y connecte avec le client ADOMD livré avec Power BI et exécute les requêtes de `powerbi/controles/*.dax`.
`scripts/concordance.py` compare ensuite chaque valeur à la même définition recalculée en SQL sur DuckDB.
Résultat : 129 comparaisons, 15 mesures, 10 contextes de filtre, aucune différence au-delà de l'arrondi des
flottants (écart relatif maximal 4,8e-14), dans `results/concordance_dax_powerbi.csv`.

Une précision que j'assume : sur la ligne « Total » du tableau par année, les mesures d'évolution comparent la
valeur toutes années confondues à celle de l'année précédente, ce qui n'a pas de sens métier. C'est le
comportement normal de `MAX(dim_date[annee])` hors contexte d'année ; je le documente au lieu de le masquer.

## 8. Comment la démo peut-elle être publique sans serveur ni compte ?

Avec stlite : Streamlit compilé en WebAssembly, exécuté par Pyodide dans le navigateur. La page de GitHub Pages
charge `streamlit_app.py` et six CSV d'agrégats (environ 150 Ko au total), et tout le calcul se fait côté client.
Aucun serveur à maintenir, aucun compte tiers, et surtout aucune donnée individuelle envoyée : seuls des agrégats
sont publiés, conformément aux conditions d'utilisation DVF, avec une balise `noindex` sur les pages. Le coût est
le premier chargement, environ 30 Mo de Python. La même application se lance en local avec
`uv run streamlit run app/streamlit_app.py`, sans modification.
