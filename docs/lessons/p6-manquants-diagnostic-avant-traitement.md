Diagnostiquer la structure des manquants avant de choisir le traitement : des trous de plusieurs jours se traitent par une règle de couverture, pas par une imputation.

# Valeurs manquantes : diagnostiquer, puis décider

**Trois mesures à produire avant toute décision.**

1. **Taux par série, par période et par heure de la journée.** Le khi-deux d'indépendance entre
   l'heure locale et le fait d'être manquant a donné p = 5,1e-60 sur les mesures de l'Hérault : les
   maintenances tombent le matin, le manque n'est pas uniforme.
2. **Longueur des séquences manquantes** (`rle(is.na(x))`). Ici 1,78 % d'heures manquantes seulement,
   mais 67,5 % de ces heures appartiennent à des trous d'au moins 24 heures. Un taux global faible ne
   dit rien de la structure.
3. **Test MCAR de Little** (`naniar::mcar_test`) sur les polluants mesurés simultanément à une même
   station : rejeté (p < 2,2e-16). L'hypothèse « manquant complètement au hasard » ne tient pas.

**Décision qui en découle.** Pas d'imputation pour les chiffres publiés. Le domaine fournit souvent
une règle meilleure qu'un modèle : en qualité de l'air, une moyenne journalière n'est calculée que si
18 heures sur 24 sont valides (75 %), et le maximum journalier de l'ozone sur 8 heures exige 6 heures
valides par fenêtre et 18 fenêtres valides par jour. Résultat : 98,0 % des jours-séries restent
exploitables et les 255 jours écartés le sont pour une raison explicite.

**Distinguer manquant et non mesuré.** Une station qui ne mesurait pas encore un polluant produit des
lignes vides dans le flux. Borner chaque série à sa première et sa dernière mesure valide évite de
compter une mise en service comme une panne : sur ce jeu, cela faisait passer le taux de manquants
de 8,5 % à 1,8 %.

**Ne garder l'interpolation que là où elle est indispensable.** La décomposition STL exige une série
continue : interpolation linéaire limitée aux trous d'au plus deux semaines, sur les moyennes
hebdomadaires, et surtout aucune alimentation des tests par ces valeurs interpolées.
