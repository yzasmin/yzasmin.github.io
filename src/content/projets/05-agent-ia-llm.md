---
slug: agent-ia-llm
titre: 'Assistant RE2020 : RAG hybride et agent outillé'
ordre: 5
categorie: 'IA générative'
famille: science
resume: 'Un assistant qui répond aux questions sur la réglementation RE2020 à partir des textes officiels, cite ses sources et refuse quand elles ne suffisent pas.'
statut: en-cours
motif: graph
stack: ['Python', 'uv', 'rank-bm25', 'fastembed ONNX', 'Qdrant', 'Ollama', 'Qwen2.5 1,5 Md', 'pytest']
liens:
  github: 'https://github.com/yzasmin/re2020-assistant-rag'
teaser: 'video/projets/agent-ia-llm.mp4'
metriques:
  - { label: 'Rappel@10, recherche hybride', valeur: '0,875' }
  - { label: 'MRR, recherche hybride', valeur: '0,541' }
  - { label: 'nDCG@10, recherche hybride', valeur: '0,614' }
  - { label: 'Passages officiels indexés', valeur: '694' }
---

## Contexte et problème

Les règles de la RE2020, la réglementation environnementale des bâtiments neufs, sont réparties entre le
code de la construction et de l'habitation, l'arrêté du 4 août 2021 et ses onze annexes, et un guide
ministériel de 93 pages. Une question aussi banale que « quel seuil carbone pour une maison dont le
permis est déposé en 2025 ? » suppose de retrouver le bon tableau, le bon usage de bâtiment et la bonne
tranche d'années. Un assistant généraliste répond de mémoire, sans source, sans distinguer les versions
successives des textes : sur un sujet réglementaire, la réponse est inexploitable.

Le projet construit l'assistant inverse : il ne répond qu'à partir de passages réellement retrouvés dans
les textes officiels, cite le document et l'article utilisés, et refuse quand les sources ne couvrent pas
la question.

## Données

Douze documents officiels téléchargés par script le 22 septembre 2026, avec leur empreinte SHA-256 dans
un manifeste : l'arrêté du 4 août 2021 en version consolidée au 19 mars 2026 (articles 1 à 52), huit de
ses annexes, l'annexe de l'article R. 172-4 du code de la construction et de l'habitation dans sa version
applicable au 1er juillet 2026, et le guide RE 2020 du ministère (Cerema, janvier 2024).

Les fichiers du portail RT-RE bâtiment sont sous Licence Ouverte Etalab 2.0. Légifrance refuse les
téléchargements automatisés, la version consolidée de l'arrêté est donc reprise de la base AIDA de
l'Ineris, chaque passage citant l'URL Légifrance de référence. Après extraction et découpage par
section : 694 passages, 726 000 caractères, chacun portant son document, sa section, sa page et son URL
pour pouvoir être cité.

## Approche

Deux moteurs de recherche indépendants sur le même corpus. Le lexical est un BM25 avec normalisation
française maison : accents retirés, élisions coupées, mots vides français, racinisation Snowball, mais
identifiants réglementaires conservés intacts, sans quoi Bbio_max, Q4Pa-surf ou 0,60 seraient détruits
par la racinisation. Le sémantique est un index vectoriel construit avec le modèle multilingue
e5-small exporté en ONNX quantifié en entiers 8 bits, stocké dans un Qdrant en mode local sur disque. Les
deux classements sont fusionnés par Reciprocal Rank Fusion.

Au-dessus, un agent appelle deux outils, servi par un modèle ouvert exécuté en local avec Ollama :
`rechercher_reglementation`, qui
renvoie des passages numérotés avec leur citation, et `calculer`, une évaluation arithmétique sûre qui
analyse l'arbre syntaxique de l'expression au lieu d'appeler `eval`. La consigne impose une citation par
affirmation et un refus explicite quand les passages ne suffisent pas.

L'évaluation repose sur 37 questions écrites à partir des textes, dont cinq hors périmètre auxquelles la
bonne réponse est un refus et quatre questions de calcul. Chaque question déclare la phrase exacte
attendue dans les textes plutôt qu'un identifiant de passage : le jeu reste valable si le découpage
change, et un test vérifie que chaque phrase attendue existe bien dans le corpus.

## Choix techniques

| Choix                                    | Plutôt que                        | Pourquoi                                                                                      |
| ---------------------------------------- | --------------------------------- | --------------------------------------------------------------------------------------------- |
| Recherche hybride BM25 et vectoriel      | Vectoriel seul                    | Les questions reprennent les identifiants réglementaires ; mesuré, le lexical fait mieux seul |
| e5-small ONNX quantifié int8             | Modèle d'embeddings plus grand    | 8 Go de RAM partagés, moins de 500 Mo utilisés, index construit sur CPU en 10 minutes         |
| Qdrant en mode local sur disque          | Serveur vectoriel ou FAISS        | Index de 2,8 Mo, aucun service à lancer, un clone vierge le reconstruit                       |
| Calculatrice par arbre syntaxique        | `eval` sur l'expression du modèle | Un outil exposé à un LLM ne doit jamais exécuter de code arbitraire                           |
| Phrases attendues plutôt qu'identifiants | Identifiants de passages figés    | Le jeu d'évaluation survit à un changement de découpage et reste vérifiable dans les textes   |
| Modèle ouvert local servi par Ollama     | API payante d'un fournisseur      | Aucun coût ni donnée envoyée à un tiers, au prix d'une latence et d'une qualité mesurées ici  |

## Résultats et métriques

Mesures réelles sur les 32 questions attendant une réponse, enregistrées dans
`results/retrieval_metrics.json`.

| Moteur                    | Rappel@1 | Rappel@3 | Rappel@5 | Rappel@10 | MRR   | nDCG@10 | Latence médiane |
| ------------------------- | -------- | -------- | -------- | --------- | ----- | ------- | --------------- |
| BM25 seul                 | 0,266    | 0,594    | 0,750    | 0,828     | 0,472 | 0,552   | 2,9 ms          |
| Vectoriel seul (e5-small) | 0,281    | 0,422    | 0,453    | 0,625     | 0,376 | 0,430   | 23,5 ms         |
| Hybride (RRF)             | 0,359    | 0,578    | 0,750    | 0,875     | 0,541 | 0,614   | 26,5 ms         |

Les colonnes de qualité sont déterministes et se reproduisent à l'identique. La latence dépend de la
charge du poste : sur quatre exécutions successives du 23/09/2026, la médiane va de 2,9 à 4,2 ms pour
BM25, de 23,1 à 26,3 ms pour le vectoriel et de 26,5 à 30,4 ms pour l'hybride. Le tableau reprend
l'exécution enregistrée dans le fichier, pas une moyenne.

![Comparaison des moteurs de récupération](/images/projets/agent-ia-llm/recuperation-comparaison.png)

La fusion hybride gagne sur le premier résultat, le rappel à 10, le MRR et le nDCG ; elle perd trois
points de rappel@3 face à BM25 seul, et le vectoriel ne sauve qu'une seule question sur les 32. Publier
ce détail vaut mieux que de vendre l'hybride comme une évidence : sur un corpus aussi technique, le
lexical fait l'essentiel du travail.

La partie génération tourne désormais en local, avec un modèle ouvert servi par Ollama plutôt qu'une API
payante. La première mesure, sur les 37 questions du jeu d'évaluation avec Qwen2.5 1,5 milliard de
paramètres quantifié en Q4_K_M, donne un résultat franchement mauvais et il est publié tel quel : le
modèle ne répond qu'à 3,1 % des questions du périmètre, refuse toutes les autres, rend 32 réponses sur 37
sans aucune citation, pour une latence médiane de 62,5 secondes par question
(`results/generation_metrics.json`). Seul point positif, il refuse bien 100 % des questions hors
périmètre, ce qui ne coûte rien quand on refuse presque tout.

Ce résultat interroge le montage autant que le modèle : une boucle d'appel d'outils est exigeante pour un
si petit modèle. Une seconde configuration, sans appel d'outils, qui place directement les meilleurs
passages dans l'invite, est en cours d'analyse. Tant que la comparaison des deux configurations n'est pas
terminée, aucune performance de génération n'est présentée comme définitive, et la fiche reste en cours.

## Limites et pistes d'amélioration

Le rappel@1 de 0,359 reste faible : deux tiers des questions ne trouvent pas leur source en premier
résultat, l'agent compense en recevant cinq passages et en pouvant relancer une recherche. Un
reclassement par cross-encoder et un découpage plus fin des grands tableaux sont la suite logique, car
les seuils vivent dans des tableaux à plusieurs entrées que l'extraction PDF aplatit en suites de
nombres. Trois questions ne sont trouvées par aucun moteur dans les dix premiers résultats.

Le corpus est volontairement partiel : la méthode de calcul Th-BCE et les règles Th-Bat, plusieurs
centaines de pages, ne sont pas indexées, et le décret lui-même manque faute d'accès automatisé à
Légifrance. Le guide de janvier 2024 peut contredire le texte consolidé de 2026 sur les points modifiés
depuis. Enfin, la première mesure de génération montre surtout les limites d'un modèle de 1,5 milliard de
paramètres sur un poste de 8 Go : le choix entre un modèle ouvert local et un modèle hébergé, et celui
entre agent outillé et chaîne directe, sont la réflexion en cours sur ce projet.
