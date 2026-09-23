Dans DVF, la valeur foncière est répétée sur chaque ligne d'une mutation : agréger à la mutation avant tout calcul, sinon les prix sont comptés plusieurs fois.

# Lire les DVF sans compter les prix deux fois

- Une mutation s'étale sur plusieurs lignes : une par local (maison, appartement, dépendance, local d'activité)
  **et** par nature de culture du terrain. Une maison sur une parcelle « sol » plus « jardin » donne deux lignes
  identiques côté local. `SUM(valeur_fonciere)` double alors son prix.
- Règle appliquée (`sql/02_mutations.sql` du projet 3) : dédoublonner les locaux par
  (mutation, parcelle, lot, type, surface, pièces), sommer le terrain sur les couples (parcelle, nature de culture)
  distincts, et prendre `MAX(valeur_fonciere)` par mutation, jamais la somme.
- Contrôle utile : compter par mutation le nombre de dates, de natures et de **valeurs foncières distinctes**. Sur
  l'Hérault 2021-2025, seules 4 mutations sur 178 905 sont incohérentes, ce qui valide l'hypothèse.
- Le prix au m² n'a de sens que si la mutation contient un seul logement : sinon la valeur est globale et rien ne
  permet de la ventiler. Dans l'Hérault, cette contrainte plus les autres règles laissent 58,9 % des mutations.
- Les doublons exacts existent aussi dans les fichiers : 20 254 lignes sur 412 496 (4,9 %) sont strictement
  identiques sur les 40 colonnes.
