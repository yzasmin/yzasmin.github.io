---
slug: analyse-statistique
titre: "Qualité de l'air dans l'Hérault : ce que disent vraiment les mesures"
ordre: 6
categorie: 'Statistiques'
famille: analyse
resume: 'Trois ans de mesures horaires Atmo Occitanie nettoyées et testées : manquants non aléatoires, pics isolés contre épisodes réels, saisonnalité et quatre tests dont les conditions sont vérifiées.'
statut: publie
motif: histogram
stack: ['R', 'Quarto', 'renv', 'data.table', 'naniar', 'rstatix']
liens:
  github: 'https://github.com/yzasmin/qualite-air-occitanie'
  notebook: 'https://yzasmin.github.io/qualite-air-occitanie/'
teaser: 'video/projets/analyse-statistique.mp4'
metriques:
  - { label: 'Mesures horaires analysées', valeur: '460 131' }
  - { label: 'Jours valides (règle des 75 %)', valeur: '98,0 %' }
  - { label: 'NO2 plus bas le week-end', valeur: '5 stations sur 8' }
  - { label: 'Ozone été contre hiver', valeur: '1,42 à 1,70 fois' }
---

## Contexte et problème

Montpellier, Lunel-Viel, Agde, le Biterrois : onze sites de mesure de la qualité de l'air, quatre
polluants, des données publiques mises à jour toutes les heures. Tout est là pour répondre à des
questions simples, que n'importe quel habitant se pose : l'air est-il meilleur le week-end, l'ozone
de l'été est-il vraiment un sujet, quelle station respire le pire, et où en est-on des seuils
sanitaires.

Sauf que la donnée brute ne se laisse pas interroger directement. Les codes de validité européens
sont peu documentés, les trous de mesure durent parfois plusieurs jours, des valeurs négatives
apparaissent, et un pic isolé peut être une panne de capteur comme un vrai épisode de pollution.
Ce projet consiste donc autant à décider ce que l'on garde, et à le justifier, qu'à appliquer des
tests. Il est écrit en R et rendu avec Quarto pour que chaque chiffre soit rejouable.

## Données

- **Source principale** : flux E2 du LCSQA (base nationale Geod'air), publié sur data.gouv.fr sous
  Licence Ouverte 2.0. Un fichier national par jour d'environ 11 Mo, soit 1 096 fichiers pour
  2023-2025, filtrés à la volée sur l'organisme Atmo Occitanie et les stations de l'Hérault :
  **477 820 mesures horaires lues**, 21 séries station-polluant, 11 sites, NO2, O3, PM10 et PM2,5.
  Sur ces lignes, 17 689 portent un code de validité invalidé et sont mises à NA dès la lecture :
  les analyses portent donc sur **460 131 mesures valides**, et c'est ce chiffre qui est mis en tuile.
- **Source de contrôle** : moyennes journalières publiées par Atmo Occitanie sur son portail open
  data (service ArcGIS, ODbL 1.0), qui couvrent aussi Béziers et Sète, absentes du flux E2.
- **Volume** : 12 Go transférés, 119 Mo conservés après filtrage, rien de commité (`data/` est
  reconstruit par `R/01_telecharger.R`).

Trois pièges ont structuré le nettoyage. D'abord, les horodatages sont en temps universel : sans
conversion en heure locale, les profils horaires sont décalés d'une à deux heures. Ensuite, le code
de validité 4 (« valide, ozone, section efficace CCQM.O3.2019 ») apparaît en 2025 et concerne 16,7 %
des heures d'ozone : filtrer naïvement sur `validité == 1` effacerait six mois d'ozone sur les cinq
stations. Enfin, une période où une station ne mesure pas encore un polluant n'est pas une donnée
manquante : les séries sont bornées à leur première et dernière mesure valide.

![Heures manquantes par mois et par série](/images/projets/analyse-statistique/manquants.png)

## Approche

1. **Diagnostic des manquants.** Taux par série, par polluant, par mois et par heure de la journée,
   longueur des séquences manquantes, test MCAR de Little. Résultat : 1,78 % d'heures manquantes,
   mais 67,5 % de ces heures appartiennent à des trous d'au moins 24 heures, le manque dépend de
   l'heure de la journée (khi-deux, p = 5,1e-60) et Little rejette MCAR (p < 2,2e-16).
2. **Traitement justifié, sans imputation aveugle.** Aucune imputation pour les chiffres publiés :
   la règle réglementaire des 75 % (18 heures valides sur 24) décide si une statistique journalière
   existe. Seule la décomposition STL, qui exige une série continue, interpole des moyennes
   hebdomadaires, sur des trous d'au plus deux semaines, et n'alimente aucun test.
3. **Valeurs aberrantes.** 302 valeurs légèrement négatives (minimum -1,8 µg/m³) sont conservées :
   ce sont des fluctuations d'analyseur autour de zéro, les tronquer biaiserait les moyennes vers le
   haut. Aucune valeur hors plage physique. Sur 4 pics candidats (au-dessus du quantile 99,9 % et
   plus de trois fois leurs heures voisines), 1 est corroboré par une autre station, donc conservé
   comme épisode réel, et 3 sont écartés.
4. **Contrôle externe.** Les moyennes journalières recalculées sont comparées à celles publiées par
   Atmo Occitanie : corrélation d'au moins 0,9915 sur 13 936 jours, 87,1 % des écarts sous 1 µg/m³.
5. **Saisonnalité.** Profils mensuel, hebdomadaire et horaire, puis STL sur les moyennes
   hebdomadaires.
6. **Tests.** Quatre questions, chacune avec ses conditions vérifiées avant le choix du test.

![Décomposition STL](/images/projets/analyse-statistique/stl.png)

## Choix techniques

| Choix                                    | Plutôt que                                 | Pourquoi                                                                                                                                |
| ---------------------------------------- | ------------------------------------------ | --------------------------------------------------------------------------------------------------------------------------------------- |
| Flux E2 horaire (LCSQA)                  | Moyennes journalières du portail Atmo      | Seules les données horaires permettent la règle des 75 %, les profils horaires et le diagnostic des trous ; le portail sert de contrôle |
| Agrégation hebdomadaire avant test       | Tests sur les valeurs journalières         | L'autocorrélation de rang 1 passe de 0,69 à 0,54 et les effectifs cessent d'être artificiellement gonflés                               |
| Wilcoxon apparié et Friedman             | Test t et ANOVA                            | Normalité rejetée (Shapiro-Wilk) et variances inégales (Levene) ; l'appariement par semaine neutralise en plus la météo                 |
| Aucune imputation, règle des 75 %        | Imputation par la moyenne ou interpolation | Les manques sont des blocs de plusieurs jours : les imputer inventerait la dynamique à décrire                                          |
| data.table et filtrage au téléchargement | tidyverse chargé sur 12 Go                 | Chaque fichier national est lu puis supprimé : 119 Mo conservés, moins de 1 Go de RAM utilisé                                           |
| renv figé sur un instantané Posit        | CRAN courant                               | R 4.2 n'a plus de binaires sur CRAN ; l'instantané du 01/04/2024 rend `renv::restore()` reproductible sans compilation                  |

## Résultats et métriques

**Test 1, NO2 semaine contre week-end** (Wilcoxon apparié sur les semaines calendaires, correction
de Holm sur 8 stations) : la baisse est significative sur 5 stations, baisse médiane 12,3 %, tailles
d'effet r de 0,21 à 0,55. Ce compte de 5 dépend du périmètre de correction retenu : en appliquant Holm
non plus par famille de tests mais aux 54 tests confirmatoires du rapport, Agathois-piscénois passe de
p = 0,031 à p = 0,079 et le compte tombe à 4 sur 8 (`results/holm_perimetre.csv`). Le résultat intéressant est négatif : à Montpellier Liberté, l'axe le plus
circulé, l'écart n'est que de 5,3 % et n'est pas significatif après correction (p = 0,107). Ce
non-rejet ne dit pas qu'il n'y a pas de baisse : l'écart de Hodges-Lehmann y est estimé à 2,2 µg/m³,
IC95 [0,0 ; 4,4], donc compatible avec une baisse réelle, simplement non établie. Et ce qui est
comparé ici est un contraste semaine / week-end sur des concentrations, pas un effet du trafic :
aucun comptage routier n'entre dans l'analyse, et le jour de la semaine est aussi un indicateur du
chauffage tertiaire, des livraisons et des chantiers.

**Test 2, ozone selon la saison** (Kruskal-Wallis puis Dunn, moyennes hebdomadaires du maximum
journalier sur 8 heures) : Shapiro-Wilk rejette la normalité dans 4 groupes station-saison sur 20 et
Levene l'égalité des variances sur 2 stations sur 5, d'où le non paramétrique. Effet très large,
epsilon² d'au moins 0,527, ozone 1,42 à 1,70 fois plus élevé en été qu'en hiver, contraste été-hiver
significatif après Holm sur les cinq stations.

**Test 3, différences entre stations** (Friedman, blocs = 144 semaines communes, post-hoc Wilcoxon
apparié et Holm) : khi-deux = 510,0, p = 4,6e-109, W de Kendall = 0,885 et 10 paires sur 10
significatives. Le classement est stable d'une semaine à l'autre : la station trafic Liberté affiche
2,8 fois la moyenne des deux stations de fond urbain de Montpellier.

**Test 4, Béziers contre Montpellier** (Wilcoxon apparié sur 58 semaines communes, données
journalières Atmo) : 16,8 contre 46,0 µg/m³ de NO2, écart de Hodges-Lehmann -28,8 µg/m³
(IC95 -30,6 à -27,1), r = 0,87.

**Seuils** : sur 41 couples station-année évaluables, 3 dépassent la valeur limite annuelle
européenne (NO2 à Montpellier Liberté, 3 années sur 3) mais 31 dépassent la ligne directrice OMS
2021, dont 11 sur 11 pour les PM2,5. La conformité réglementaire est presque acquise, la conformité
sanitaire au sens de l'OMS ne l'est pas.

![Moyennes annuelles face aux seuils](/images/projets/analyse-statistique/seuils.png)

![Profil horaire du NO2](/images/projets/analyse-statistique/profil_horaire_no2.png)

## Impact métier

Pour le service environnement ou santé-environnement d'une collectivité, et pour une équipe de recherche
qui travaille sur l'exposition des habitants, deux décisions sont en jeu : sur quel périmètre agir, et
sur quel seuil communiquer.

- **Mesuré** : 3 couples station-année sur 41 dépassent la valeur limite annuelle européenne, mais 31 sur
  41 dépassent la ligne directrice de l'OMS 2021, dont 11 sur 11 pour les particules fines PM2,5
  (`results/seuils_oms_ue.csv`). Être en règle au sens européen ne veut donc pas dire être en règle au
  sens sanitaire, et c'est le message que cette analyse permet de porter avec ses chiffres.
- **Mesuré** : la station de trafic Montpellier Liberté affiche 2,8 fois la moyenne des deux stations de
  fond urbain de la même ville, 42,9 contre 17,4 et 13,1 µg/m³
  (`results/test3_no2_stations_moyennes.csv`). L'action utile se décide à l'échelle d'un axe de
  circulation, pas de la commune entière.
- **Mesuré** : les 460 131 mesures horaires retenues sont contrôlées contre les moyennes publiées par
  Atmo Occitanie, avec une corrélation d'au moins 0,9915 sur 13 936 jours
  (`results/controle_atmo_journalier.csv`), et le rapport entier se recalcule en environ 4 minutes : il
  se remet à jour à chaque nouvelle année de données.

## Limites et pistes d'amélioration

- Trois années seulement : une tendance pluriannuelle ne se distingue pas d'un effet météorologique,
  aucune tendance n'est donc testée ici.
- Aucune covariable météo (température, vent, pluie) : les écarts entre stations mélangent émissions
  et dispersion. Ajouter les données Météo-France ouvrirait la porte à une régression avec effets
  station et saison.
- Béziers et Sète ne sont pas dans le flux E2 : le test 4 repose sur 58 semaines de moyennes
  journalières et compare deux points de mesure, pas deux villes.
- L'agrégation hebdomadaire réduit l'autocorrélation des **niveaux** sans l'annuler (0,69 en
  journalier, 0,54 en hebdomadaire). Mais pour un test apparié, la quantité qui compte est
  l'autocorrélation des **différences**, que le code calcule : elle va de -0,45 à -0,10 sur les huit
  stations du test 1, donc l'appariement absorbe la dépendance et ces p-valeurs ne sont pas
  optimistes ; elle vaut 0,22 sur 58 semaines au test 4, ce qui élargit l'intervalle d'environ 25 %
  sans changer la conclusion. La réserve vaut en réalité pour les tests 2 et 3, qui traitent comme
  indépendantes des semaines consécutives qui ne le sont pas : Friedman est un test par blocs, il
  n'est pas exonéré par l'appariement, et le contraste saisonnier ne repose que sur 12 blocs
  saison-année réellement indépendants. Ce sont les tailles d'effet qui portent ces deux conclusions,
  pas les p-valeurs.
- La correction de Holm est appliquée **par famille**, une famille par question de recherche, pas
  globalement. `results/holm_perimetre.csv` chiffre ce choix : sous un contrôle global des 54 tests
  confirmatoires, 4 tests changent de statut et le « 5 stations sur 8 » du test 1 deviendrait
  « 4 sur 8 » (Agathois-piscénois passe de p = 0,031 à p = 0,079).
- Le passage de l'ozone à la section efficace CCQM.O3.2019 en 2025 crée une rupture de comparabilité
  de quelques pour cent qui n'est pas corrigée.
