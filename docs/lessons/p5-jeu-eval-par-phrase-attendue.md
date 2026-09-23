Décrire les passages attendus d'un jeu d'évaluation RAG par une phrase exacte du texte, et non par un identifiant de passage, rend le jeu robuste au redécoupage et vérifiable.

# Ancrer un jeu d'évaluation RAG dans le texte, pas dans le découpage

- Piège classique : écrire `"gold_chunks": ["doc#0042"]`. Le moindre changement de taille de passage ou
  de règle de titre décale tous les identifiants et le jeu devient silencieusement faux.
- Solution retenue : chaque question déclare `{"doc": "...", "contient": "phrase exacte du texte"}`.
  À l'évaluation, les passages pertinents sont ceux du bon document dont le texte normalisé contient la
  phrase normalisée (accents, espaces insécables et lettres mathématiques des PDF ramenés en ASCII).
- Bénéfice secondaire : un test automatique vérifie que chaque phrase attendue existe dans le corpus.
  Une phrase introuvable signale soit une référence inventée, soit une phrase coupée par une frontière
  de passage, et les deux méritent une correction.
- Définitions retenues pour les métriques, à écrire dans le code plutôt que dans le README :
  rappel@k = part des sources attendues dont au moins un passage figure dans les k premiers résultats,
  MRR = inverse du rang du premier passage attendu, nDCG@10 binaire avec une seule prise en compte par
  source attendue.
- Les questions hors périmètre n'ont pas de passage attendu : les exclure des métriques de récupération
  et ne les utiliser que pour le taux de refus correct.
