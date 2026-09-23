Préparation d'entretien pour le projet 1 (EMOTIC, contexte de scène) : 8 questions probables et réponses courtes, fondées sur le code et le poster.

# Entretien : projet de fin d'études EMOTIC

Dépôt : https://github.com/yzasmin/emotic-emotions-contexte. Chiffres : poster du projet (`results/resultats_poster.json`).

## 1. Pourquoi ajouter un vecteur de 114 dimensions plutôt qu'un réseau plus gros ?

Pour que l'apport du contexte soit mesurable et lisible. Chaque dimension a un sens : 80 scores d'objets YOLOv8,
5 couleurs dominantes (15 valeurs), un histogramme HSV (16) et luminosité, contraste, saturation (3).
On peut ensuite dire quel objet pousse vers quelle émotion, ce qu'un troisième CNN ne permet pas.
Avant d'entraîner, une preuve de concept a montré que 654 paires objet × émotion sur 2 080 (31,4 %) étaient
significatives sur les annotations, contre environ 5 % attendus par hasard.

## 2. +0,49 point de mAP, est-ce significatif ?

Honnêtement, on ne peut pas l'affirmer. C'est une seule exécution par modèle, sans plusieurs graines ni intervalle
de confiance. Le protocole est propre (même backbone, même entraînement, seule la branche change), mais pour conclure
il faudrait répéter l'entraînement avec plusieurs graines ou faire un bootstrap sur le test set. Le gain est aussi
inégal : +0,038 d'AP environ sur Suffering, recul sur Annoyance, Affection, Peace.

## 3. Pourquoi êtes-vous sous le papier original (25,60 % contre 27,38 %) ?

Plusieurs différences : nos backbones de contexte sont pré-entraînés sur ImageNet et non sur Places365, nos pertes
diffèrent (Focal Loss et MSE au lieu d'une perte euclidienne pondérée et de SL1), et les réglages (25 époques,
batch de 16) ne font l'objet d'aucune recherche d'hyperparamètres dans les notebooks. Le vecteur YOLO est aussi creux : 24 % des images n'ont aucun objet détecté au-dessus de 0,25.

## 4. Comment avez-vous géré le déséquilibre des classes ?

Deux leviers dans le code : un `WeightedRandomSampler` dont le poids de chaque exemple est l'inverse de la fréquence de
sa catégorie la plus rare, et une Focal Loss (α = 0,25, γ = 2). Ça compense en partie : Engagement dépasse
12 000 annotations quand Pain, Embarrassment et Suffering restent sous 500, et ces classes rares ont toujours des AP faibles.

## 5. Pourquoi la mAP comme métrique ?

C'est un problème multi-label (une personne peut avoir plusieurs émotions) et très déséquilibré. L'AP par classe ne
dépend pas d'un seuil et la moyenne donne le même poids à chaque émotion. C'est aussi la métrique du papier de
référence, ce qui rend la comparaison possible. Pour VAD, on rapporte MAE et corrélation de Pearson.

## 6. Qu'avez-vous appris de l'interprétabilité ?

Deux choses. Côté résultat, 1 235 paires sur 2 080 sont significatives dans les prédictions, avec une corrélation
maximale de 0,49 (par exemple person et Yearning, +0,38). Côté limite, l'attention sur les objets apprend des poids
presque uniformes, environ 1/80 : elle ne sélectionne pas vraiment. Corrélation ne veut pas dire causalité, et les
associations sport (gant, batte) reflètent aussi la composition du jeu de données.

## 7. Les notebooks n'ont aucune sortie : comment garantir que le code publié correspond ?

Je l'ai dit clairement dans le README : les chiffres viennent du poster. Pour le code, j'ai isolé les architectures dans
un module et écrit un test de fumée qui tourne sur CPU sans données : il vérifie les sorties (26 logits, 3 valeurs VAD)
et exécute les définitions de classes de chaque notebook pour contrôler qu'elles produisent les mêmes couches.
En recomptant, j'ai trouvé des écarts avec le schéma du poster (tête de fusion de 1,94 million de paramètres, pas 1,16),
que j'ai documentés plutôt que corrigés.

## 8. Qu'a apporté la partie génération d'images ?

C'est une perspective, pas un résultat. Pour 6 émotions, on génère une image par transfert de style photométrique ou
par Stable Diffusion v1.5 avec un prompt construit à partir de la matrice objet × émotion, puis B+I+Y la reclasse.
Le poster ne chiffre pas de taux de réussite, et les profils de couleur ont été saisis à la main depuis un graphique.
Ce que ça montre : on peut fermer la boucle entre ce que le modèle a appris et une génération contrôlée.
