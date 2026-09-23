Sur des séries temporelles, agréger à la semaine et apparier dans la même semaine avant de tester : sinon les p-valeurs mesurent surtout la taille de l'échantillon.

# Autocorrélation et tests d'hypothèses

**Le problème.** Les tests classiques (t, Wilcoxon, Kruskal-Wallis) supposent des observations
indépendantes. Des mesures horaires ou journalières de pollution ne le sont pas : l'autocorrélation de
rang 1 médiane est de 0,69 sur les valeurs journalières de ce projet. Avec 1 100 jours, n'importe
quelle différence devient « significative » alors que l'information effective vaut peut-être 150
observations.

**Ce qui a été fait.**

- Mesurer l'autocorrélation avant de tester (`acf(x, lag.max = 1)`) et la publier : 0,69 en journalier,
  0,54 en hebdomadaire.
- Agréger à la semaine, unité qui conserve le signal cherché (saison, jour ouvré) en réduisant la
  dépendance.
- Apparier à l'intérieur de la même semaine quand la question s'y prête : comparer jours ouvrés et
  week-end de la même semaine neutralise la météo et la saison, et l'autocorrélation des différences
  tombe entre -0,45 et -0,10.
- Quand plusieurs groupes partagent les mêmes semaines (plusieurs stations, mêmes dates), utiliser
  Friedman avec les semaines en blocs plutôt que Kruskal-Wallis, qui jetterait l'appariement.
- Publier une taille d'effet à côté de chaque p-valeur (r de Wilcoxon, epsilon² de Kruskal-Wallis,
  W de Kendall pour Friedman) et corriger les tests multiples par Holm.

**Ce qu'il reste à dire.** L'agrégation réduit l'autocorrélation sans l'annuler : l'écrire dans les
limites du rapport est plus honnête que de prétendre l'avoir traitée. Un modèle avec structure
d'erreur temporelle (GLS, blocs bootstrap) serait la suite logique.
