Pour prouver qu'un module recopié est fidèle aux notebooks, exécuter seulement leurs `ClassDef` (ast) avec un faux `torchvision.models` sans poids, puis comparer les formes du state_dict.

# Test de fidélité notebooks / module

- Lire les cellules de code du `.ipynb` (json), retirer les lignes `!pip` et `%magic`, `ast.parse`, garder les
  nœuds `ast.ClassDef`, puis `exec` dans un espace qui contient les constantes, `torch`, `nn` et un proxy
  `models` (`SimpleNamespace(resnet50=lambda weights=None: tv.resnet50(weights=None), ...)`).
- Comparer `{k: v.shape for k, v in state_dict().items()}` entre la classe du notebook et celle de `src/` :
  des couches différentes ou renommées font échouer le test. Voir `tests/test_architectures.py` du projet 1.
- Mémoire : EfficientNet-B2 + ResNet-50 font 33 M de paramètres, environ 130 Mo par modèle en float32.
  Instancier, comparer, `del`, `gc.collect()` : 12 tests passent en 3 min 30 sur CPU avec moins de 1 Go libre.
- Le recomptage a révélé que le schéma du poster annonçait 1,16 M pour la tête de fusion quand le code en a 1,94 M
  (FC 512 puis 256, pas deux FC 256) : documenter l'écart, ne pas corriger le chiffre.
