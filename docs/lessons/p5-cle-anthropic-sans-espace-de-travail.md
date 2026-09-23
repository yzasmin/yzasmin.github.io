Une clé Anthropic non rattachée à un espace de travail fait échouer tous les appels en 400 tant que l'en-tête anthropic-workspace-id n'est pas envoyé.

# Clé API présente mais inutilisable

- Symptôme : `anthropic.BadRequestError: 400 ... This API key is not scoped to a workspace, so this
request must include the anthropic-workspace-id header with the ID of the workspace to use.`
- La clé est bien lue (pas d'erreur d'authentification) : le problème n'est ni le `.env`, ni
  `python-dotenv`, ni la variable `ANTHROPIC_BASE_URL`.
- Correction côté code : construire le client avec
  `anthropic.Anthropic(default_headers={"anthropic-workspace-id": os.environ["ANTHROPIC_WORKSPACE_ID"]})`
  et documenter la variable dans `.env.example`. L'identifiant se lit dans la console Anthropic.
- Conséquence pour un projet de portfolio : distinguer dans le README « code prêt, non exécuté » de
  « mesuré ». Publier une estimation de coût calculée sur les vrais prompts est acceptable si elle est
  étiquetée estimation ; publier un taux de fidélité sans appel réel ne l'est pas.
- Garde-fou utile : une commande d'évaluation qui s'arrête avec un message clair et un code de sortie 1
  quand la clé manque, plutôt qu'une exécution partielle qui écrirait des fichiers de résultats vides.
- Suite donnée dans le projet 5 : la couche de génération a finalement été basculée sur un modèle ouvert
  exécuté en local (Ollama), ce qui supprime la dépendance à une clé et le coût, au prix de la latence.
  Le même garde-fou sert alors à vérifier que le serveur local répond et que le modèle est téléchargé.
