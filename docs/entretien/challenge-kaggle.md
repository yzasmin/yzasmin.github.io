Huit questions probables d'un recruteur technique sur le challenge GeoLifeCLEF 2025 (projet 2), avec des réponses courtes fondées sur le dépôt et la présentation.

# Entretien : challenge Kaggle GeoLifeCLEF 2025

Sources des chiffres : présentation de soutenance (numéro de page indiqué) et dépôt
`github.com/yzasmin/geolifeclef-2025-multimodal` (`results/scores_kaggle.json`).

## 1. Quel était votre rôle exact dans une équipe de cinq ?

J'étais à la modélisation avec Adrian ; les trois autres travaillaient sur l'interprétation (serveur MCP, statistiques,
explications par LLM). Le dossier `yasmina/` du dépôt contient la V1 : même architecture que le modèle de départ,
mais entraînement sur 5 plis spatiaux avec moyenne des logits, warmup puis cosinus, clipping et 50 époques
(0,200 à 0,22793). Il contient aussi le modèle avec la branche EfficientNet-B3 et le script de pré-entraînement
Presence-Only. Sur le planning (p. 4), j'avais aussi l'analyse des résultats, Adrian les soumissions.

## 2. Pourquoi une validation spatiale plutôt qu'un découpage aléatoire ?

Parce que le test est géographiquement décalé : 67,4 % des relevés de test sont à plus de 10 km de l'entraînement,
et l'Ukraine et la Bulgarie pèsent 42 % du test pour environ 0 % de l'entraînement (p. 9). Des relevés voisins
partagent espèces et climat : un découpage aléatoire mettrait des quasi-doublons des deux côtés et surestimerait
le score. Nous groupons les relevés par cases de 1 degré de latitude et longitude et répartissons les cases en 5 plis.

## 3. Votre validation interne donnait environ 0,35 et Kaggle environ 0,23. Pourquoi cet écart ?

Même la validation spatiale reste dans la zone couverte par l'entraînement, alors que le test contient des pays
presque absents. Les espèces rares pèsent aussi : environ 3 200 espèces ont moins de 10 occurrences dans un des
splits d'entraînement. Le score interne sert donc à comparer des variantes entre elles, pas à prédire le score Kaggle.

## 4. Pourquoi EfficientNet-B3 plutôt que ConvNeXt ou Swin ?

Nous avons testé six encodeurs d'image (p. 8). EfficientNet-B3, environ 12 M paramètres, donnait le meilleur
compromis ; ResNet-50 était plus lourd sans gain net, ConvNeXt-Tiny moins stable selon les plis, Swin-Tiny pas
meilleur. Sur des patchs de 64 x 64 pixels, un modèle plus gros n'apporte pas forcément plus d'information.
Pour passer à 4 canaux, on garde les poids RGB d'ImageNet et on initialise le canal proche infrarouge avec ceux du rouge.

## 5. Le pré-entraînement Presence-Only : qu'est-ce qui a marché, qu'est-ce qui n'a pas marché ?

Pré-entraîner les encodeurs tabulaires sur 5 millions d'observations Presence-Only, puis affiner sur les relevés,
donnait 0,233. Plus long était pire : 15 époques donnaient 0,175, les encodeurs se sur-spécialisaient sur une tâche
différente. Plus de variables aussi : 57 variables auxiliaires donnaient 0,226 contre 0,233 avec 7 (p. 10).
Honnêtement, le script conservé dans le dépôt est resté réglé sur la variante à 15 époques.

## 6. Comment décidez-vous combien d'espèces prédire par relevé ?

Le F1 punit autant les oublis que les excès, et la richesse varie beaucoup d'un site à l'autre. Pour chaque relevé,
on prend la somme des probabilités sigmoïdes, on la multiplie par un facteur alpha, on borne entre 1 et 50, et on
garde les espèces les plus probables. Alpha est choisi sur une grille pour maximiser le F1 de validation.

## 7. Au classement final, une « Baseline participant random » est au-dessus de toutes les équipes. Qu'en pensez-vous ?

C'est vrai et je le dis tel quel : sur le tableau final (p. 11), la référence est à 0,21495 et nous à 0,20227. Notre
première place est un rang entre les quatre équipes, pas une victoire sur cette référence. Nous avions annoncé
0,23389 pour l'ensemble ; mon hypothèse est que c'était le classement public, calculé sur une partie du test, et que
0,20227 est le classement privé, mais je n'ai pas de fichier qui le confirme. La leçon : tout le monde a perdu entre
public et privé, ce qui montre à quel point le test était hors distribution, et il faut regarder la ligne de
référence avant de parler de performance.

## 8. Qu'avez-vous fait pour rendre ce projet publiable, et que feriez-vous autrement ?

J'ai repris le dépôt d'équipe (licence MIT) : retrait des environnements, wheels, poids, caches et soumissions,
chemins de serveur remplacés par des variables d'environnement avec un `.env.example`, scan de secrets, environnement
uv et un test de fumée qui vérifie les architectures sans données ni GPU. Autrement : garder chaque soumission avec
son script et sa configuration (aucun script ne combine V1 et V6 dans le dépôt), valider en laissant des pays
entiers de côté, et mesurer tôt l'écart avec la ligne de référence.
