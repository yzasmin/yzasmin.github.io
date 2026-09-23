Questions probables d'un recruteur technique sur le projet 5 (assistant RAG RE2020, génération locale) et réponses fondées sur ce qui a été réellement fait et mesuré.

# Entretien : assistant RE2020 (RAG hybride et modèle local)

## 1. Pourquoi une recherche hybride plutôt qu'un simple index vectoriel ?

Parce que je l'ai mesuré, et que le résultat est contre-intuitif. Sur mes 32 questions avec passages
attendus, le vectoriel seul plafonne à 0,625 de rappel@10 et 0,430 de nDCG@10, alors que BM25 seul monte
à 0,828 et 0,552. Les questions RE2020 reprennent des identifiants réglementaires exacts, Bbio_max,
Q4Pa-surf, Icconstruction, que le lexical retrouve mieux qu'un modèle d'embeddings compact. La fusion par
Reciprocal Rank Fusion prend le meilleur des deux : 0,875 de rappel@10, 0,541 de MRR, 0,614 de nDCG@10,
et le premier résultat est le bon dans 36 % des cas contre 27 % pour BM25. Je dis aussi ce que la fusion
coûte : elle perd trois points de rappel@3 face à BM25, et le vectoriel ne sauve qu'une question sur 32.

## 2. Comment avez-vous construit le jeu d'évaluation, et comment évitez-vous de le fabriquer à votre avantage ?

37 questions écrites après lecture des textes, jamais de mémoire : seuils Cep et Icconstruction, DH,
perméabilité à l'air, éclairage naturel, attestations, champ d'application, plus quatre questions de
calcul et cinq questions hors périmètre dont la bonne réponse est un refus. Chaque question porte une
réponse de référence rédigée à partir du passage, et une phrase exacte attendue dans les textes plutôt
qu'un identifiant de passage. C'est important pour deux raisons : le jeu reste valable si je change le
découpage, et un test automatique vérifie que chaque phrase attendue existe bien dans le corpus, donc
je ne peux pas inventer une référence introuvable.

## 3. Pourquoi un modèle local plutôt qu'une API ?

C'est le choix retenu avec la contrainte du poste : pas de clé exploitable, et l'envie d'un projet
reproductible par n'importe qui sans compte ni budget. Toute la génération tourne sur Ollama avec
Qwen2.5 1,5 milliard de paramètres quantifié en Q4_K_M, soit 986 Mo sur le disque et environ 1,2 Go en
mémoire. Le coût d'exécution est nul et aucune donnée ne sort du poste, ce qui est un argument réel sur
un sujet réglementaire. Le prix à payer est la qualité et la latence, que je publie telles quelles.

## 4. Quelle était la contrainte matérielle, et qu'est-ce qu'elle a changé dans l'architecture ?

8 Go de RAM partagés, souvent moins de 500 Mo libres, et un GPU MX350 de 2 Go. J'ai commencé avec un
modèle de 3 milliards de paramètres : il pagine, 11 jetons par seconde en lecture d'invite, 3,6 en
génération, et une première question de RAG n'avait pas répondu après neuf minutes. Le même test avec le
1,5 milliard donne 218 jetons par seconde en lecture et 10,7 en génération, parce qu'il tient réellement
en mémoire. J'ai aussi réduit la fenêtre de contexte à 4096 jetons, borné la génération à 400 jetons et
gardé le modèle chargé entre les questions, ce qui économise une trentaine de secondes par question.

## 5. Un modèle de 1,5 milliard de paramètres sait-il appeler des outils ?

Mal. Il a bien la capacité déclarée, mais en pratique il n'émettait pas d'appel fiable au premier tour :
il répondait directement, ou écrivait un pseudo appel en texte. J'ai donc rendu la première recherche
déterministe, les cinq passages sont joints à la question, et je laisse les outils disponibles pour une
recherche complémentaire et pour la calculatrice. C'est un compromis assumé et documenté : la boucle
d'agent existe et est testée, mais sur ce modèle elle sert surtout de filet. Avec un modèle plus capable,
il suffirait de rendre la première recherche optionnelle.

## 6. Comment mesurez-vous la fidélité, et quelle confiance accordez-vous à ce chiffre ?

Le juge reçoit la question, la réponse de référence, les passages cités et la réponse à évaluer. Il
découpe la réponse en affirmations vérifiables et dit, pour chacune, si un passage fourni l'établit ; la
fidélité est la part d'affirmations soutenues. La sortie est contrainte par un schéma JSON, ce qui évite
d'analyser du texte libre. La confiance est limitée, et je l'écris dans le README comme dans la fiche :
le juge est le même petit modèle local que l'agent, donc il est à la fois juge et partie, et un modèle de
cette taille juge grossièrement. C'est pour cela que je publie à côté trois métriques qui ne dépendent
d'aucun modèle : le taux de citations valides, le taux de refus correct sur les questions hors périmètre
et la latence.

## 7. Comment avez-vous sécurisé l'outil de calcul exposé au modèle ?

L'expression est analysée avec le module `ast` de Python, puis évaluée nœud par nœud : seuls les nombres,
les opérateurs arithmétiques et cinq fonctions autorisées passent. Tout le reste, appels de fonction non
listés, noms de variables, compréhensions, importations, lève une erreur explicite renvoyée au modèle
comme résultat d'outil en erreur. J'ai aussi borné la longueur de l'expression et l'exposant, refusé les
résultats non finis, et géré la virgule décimale française sans casser la virgule séparatrice
d'arguments. Dix expressions malveillantes ou invalides sont testées, dont `__import__('os')`. La règle
simple : un outil appelé par un modèle ne doit jamais exécuter du code arbitraire, même pour un calcul.

## 8. Que feriez-vous ensuite, et qu'apporterait un modèle hébergé ?

Côté récupération, un reclassement des dix premiers résultats par cross-encoder, puis un traitement
spécifique des tableaux réglementaires : aujourd'hui l'extraction PDF les aplatit en suites de nombres,
un passage peut contenir le bon seuil sans que sa condition d'application reste lisible. Côté génération,
un modèle hébergé de la classe Haiku ou Sonnet changerait trois choses mesurables : des appels d'outils
fiables, donc une vraie boucle d'agent avec reformulation de requête, des réponses citées beaucoup plus
régulières, et un juge indépendant et plus capable, donc un chiffre de fidélité crédible. L'évaluation
complète coûterait alors moins d'un dollar, mais ferait sortir les questions du poste : c'est
exactement l'arbitrage que le projet documente.
