Préparation d'entretien pour le projet 6 (qualité de l'air dans l'Hérault) : huit questions probables et des réponses courtes, appuyées sur ce qui a été réellement fait.

# Entretien : projet qualité de l'air (R, Quarto)

## 1. Pourquoi ne pas avoir imputé les valeurs manquantes ?

Parce que le diagnostic montre que ce ne sont pas des trous isolés : 67,5 % des heures perdues
appartiennent à des séquences d'au moins 24 heures, le taux de manque dépend de l'heure de la journée
(khi-deux, p = 5,1e-60) et le test MCAR de Little est rejeté (khi-deux = 388,3, ddl = 15, p < 2,2e-16).
Imputer des blocs de plusieurs jours reviendrait à inventer la dynamique que je cherche à décrire.
J'ai retenu la règle réglementaire : une statistique journalière n'existe que si 18 heures sur 24 sont
valides. Seule la décomposition STL, qui exige une série continue, interpole des moyennes
hebdomadaires sur des trous d'au plus deux semaines, et elle n'alimente aucun test.

## 2. Comment distinguez-vous une erreur de mesure d'un vrai épisode de pollution ?

Par une règle écrite et par la corroboration entre stations. Une valeur est candidate si elle dépasse
le quantile 99,9 % de sa série et vaut plus de trois fois la plus forte de ses deux heures voisines.
Elle n'est écartée que si aucune autre station du même polluant n'est au-dessus de son propre quantile
95 % à la même heure. Sur 4 candidats, 1 était corroboré (épisode partagé, conservé) et 3 ont été mis
en valeur manquante. Un épisode de particules, poussières sahariennes ou chauffage au bois, ressemble
beaucoup à une panne quand on regarde une seule station : c'est précisément ce qu'il ne faut pas
supprimer.

## 3. Pourquoi des tests non paramétriques ?

Pas par principe, mais après vérification. Pour la comparaison semaine / week-end, Shapiro-Wilk rejette
la normalité des différences sur 4 stations sur 8. Pour l'ozone, Shapiro-Wilk rejette dans 4 groupes
station-saison sur 20 et Levene rejette l'égalité des variances sur 2 stations sur 5 (p = 0,046 et 0,044). Pour la
comparaison entre stations, les résidus de l'ANOVA à mesures répétées ne sont pas normaux
(p = 8,7e-05) et les variances sont très inégales (Levene p = 1,0e-36), ce qui est logique quand les
moyennes vont de 6 à 43 µg/m³. D'où Wilcoxon apparié, Kruskal-Wallis avec post-hoc de Dunn, et
Friedman avec post-hoc de Wilcoxon apparié.

## 4. Les mesures horaires sont autocorrélées : comment avez-vous traité l'indépendance ?

C'est le point le plus délicat du projet, et il faut distinguer deux quantités. L'autocorrélation de
rang 1 des **niveaux** vaut 0,69 en médiane sur les valeurs journalières et 0,54 sur les moyennes
hebdomadaires : c'est ce qui justifie d'agréger à la semaine. Mais pour un test **apparié**, la quantité
qui décide de la validité est l'autocorrélation des **différences**, et le code la calcule aussi.

Pour le test 1, elle va de -0,45 à -0,10 sur les huit stations : elle est négative partout, donc
l'appariement absorbe la dépendance temporelle et l'effectif effectif est supérieur à n. Ces p-valeurs
ne sont pas optimistes. Pour le test 4, elle vaut 0,22 sur 58 semaines, soit un effectif effectif
d'environ 37 et un intervalle élargi d'environ 25 % : l'intervalle de Hodges-Lehmann [-30,6 ; -27,1]
passe à environ [-31,0 ; -26,6], la conclusion ne bouge pas.

La vraie réserve est ailleurs, et je la nomme maintenant explicitement : elle porte sur les tests 2 et
3, qui traitent comme indépendantes des semaines consécutives qui ne le sont pas. Friedman est un test
**par blocs**, il suppose l'indépendance des blocs, donc des semaines, et il n'est pas exonéré par
l'appariement comme je l'écrivais avant. Sa p-valeur de 4,6e-109 est un plancher numérique, pas une
mesure d'évidence : la statistique V du post-hoc vaut 10 440 pour plusieurs paires, soit exactement le
maximum arithmétique 144 x 145 / 2. Et sur trois ans, le contraste saisonnier du test 2 ne repose que
sur 12 blocs saison-année réellement indépendants. Dans les deux cas la conclusion tient, mais c'est la
taille d'effet qui la porte (W de Kendall 0,885, epsilon² au moins 0,527), pas la p-valeur.

## 5. Quelle correction pour les tests multiples, et pourquoi ?

La méthode de Holm, appliquée par famille de tests : 8 stations pour la comparaison semaine /
week-end, 5 stations pour l'ozone, 10 paires pour les comparaisons entre stations, et à l'intérieur du
post-hoc de Dunn. Holm contrôle le taux d'erreur familial comme Bonferroni mais est uniformément plus
puissant. Avec une dizaine de tests, contrôler le FWER reste raisonnable ; si j'avais eu des centaines
de comparaisons, j'aurais utilisé Benjamini-Hochberg pour contrôler le taux de fausses découvertes.

Ce périmètre est un choix, et je le déclare maintenant dans le rapport plutôt que de le laisser
implicite, parce qu'il n'est pas neutre. Je l'ai chiffré : `results/holm_perimetre.csv` rejoue la
correction sur les 54 tests confirmatoires du rapport pris ensemble. Sous ce contrôle global,
4 tests changent de statut, dont la station Agathois-piscénois qui passe de p = 0,031 à p = 0,079 :
le « 5 stations sur 8 » du test 1 deviendrait « 4 sur 8 ». Si un statisticien me pose la question, la
réponse est écrite et le fichier est dans le dépôt.

## 6. Quel résultat vous a surpris ?

Le résultat négatif du test 1. On s'attend à ce que le NO2 baisse partout le week-end, et c'est vrai sur
5 stations sur 8 avec une baisse médiane de 12,3 %. Mais à Montpellier Liberté, la station trafic de
l'axe le plus circulé, la baisse n'est que de 5,3 % et n'est plus significative après correction de Holm
(p = 0,107). Je l'ai publié tel quel, mais je fais attention à ce que j'en conclus. D'abord, un
non-rejet n'est pas une preuve d'absence : l'écart de Hodges-Lehmann y est estimé à 2,2 µg/m³ avec un
IC95 [0,0 ; 4,4], donc parfaitement compatible avec une baisse réelle allant jusqu'à 4,4 µg/m³, elle
n'est simplement pas établie après correction sur les huit stations. Ensuite, ce que je mesure est un
contraste semaine / week-end sur des concentrations, pas un effet du trafic : aucun comptage routier
n'entre dans l'analyse, et le jour de la semaine est aussi un indicateur du chauffage tertiaire, des
livraisons, des chantiers et du transit. Deux autres stations ne sont pas significatives simplement
parce que leur série est trop courte (15 et 50 semaines).

## 7. Comment savez-vous que votre nettoyage est correct ?

Je le confronte à une source indépendante. Les moyennes journalières que je recalcule à partir des
heures sont comparées à celles publiées par Atmo Occitanie sur son portail open data : corrélation d'au
moins 0,9915 sur 13 936 jours et 87,1 % des écarts sous 1 µg/m³. Les écarts résiduels s'expliquent par
la définition du jour (j'agrège en heure locale) et par des validations postérieures intégrées par le
producteur. C'est un contrôle, pas une copie : je n'utilise pas leurs agrégats pour produire les miens.

## 8. Pourquoi R et Quarto, et est-ce reproductible ?

R parce que l'écosystème statistique y est plus direct : `naniar` pour le diagnostic des manquants et le
test de Little, `rstatix` pour les tailles d'effet et le post-hoc de Dunn, `car` pour Levene, `stl` en
base. Quarto parce que le rapport HTML autonome contient le texte, le code replié et les figures, donc
rien ne peut diverger entre le rapport et les chiffres. La reproductibilité tient à trois choses : un
`renv.lock` figé sur un instantané Posit Package Manager (R 4.2 n'a plus de binaires sur CRAN), un
script de téléchargement repris là où il s'est arrêté, et des sorties chiffrées écrites dans `results/`
par le code qui rend le rapport, de sorte que chaque chiffre du portfolio se retrouve dans un fichier.

## À garder en tête

- Volume : 1 096 fichiers nationaux d'environ 11 Mo lus puis supprimés, 477 820 mesures horaires
  conservées, dont 460 131 valides et réellement analysées (17 689 lignes au code de validité invalidé,
  mises à NA dès la lecture), 119 Mo sur le disque, moins de 1 Go de RAM.
- Piège à raconter : le code de validité 4 (ozone, section efficace CCQM.O3.2019, apparu en 2025) ;
  filtrer sur `validité == 1` supprime six mois d'ozone sur cinq stations sans erreur visible.
- Message métier : conformité réglementaire presque acquise (3 dépassements de valeur limite annuelle
  sur 41 couples station-année), conformité OMS 2021 très loin d'être atteinte (31 sur 41).
