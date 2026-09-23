Sur les textes RE2020, BM25 seul bat nettement un petit modèle d'embeddings multilingue ; la fusion RRF garde l'avantage, mais il faut le mesurer avant de choisir.

# Recherche lexicale contre recherche vectorielle sur un corpus réglementaire

**Mesure.** 32 questions avec passages attendus, 694 passages indexés
(`results/retrieval_metrics.json` du dépôt `re2020-assistant-rag`) :

| Moteur                    | Rappel@10 | MRR   | nDCG@10 |
| ------------------------- | --------- | ----- | ------- |
| BM25 seul                 | 0,828     | 0,472 | 0,552   |
| Vectoriel seul (e5-small) | 0,625     | 0,376 | 0,430   |
| Hybride (RRF, k = 60)     | 0,875     | 0,541 | 0,614   |

**Pourquoi.** Les questions réglementaires reprennent des identifiants exacts (Bbio_max, Q4Pa-surf,
Icconstruction, 0,60 m3/(h.m2)). Un modèle d'embeddings compact les noie dans une moyenne sémantique,
alors que BM25 les traite comme des termes rares très discriminants.

**Conséquences pratiques.**

- Garder les identifiants intacts dans la tokenisation : ne pas raciniser un jeton contenant un chiffre
  ou un souligné, sinon `Bbio_max` et `0,60` disparaissent.
- Ne pas conclure qu'un moteur est inutile sans regarder le détail question par question : ici le
  vectoriel ne sauve qu'une question sur 32, mais la fusion ne coûte que 20 ms.
- Sur un tout petit corpus de test (deux documents), BM25 attribue une pondération nulle à un terme
  présent dans la moitié des documents : prévoir au moins trois documents dans les tests, sinon le
  score est 0 et le test échoue pour une raison qui n'a rien à voir avec le code.
