Sur un poste à 8 Go de RAM, un modèle de 3 milliards de paramètres pagine (3,6 jetons/s en sortie) alors que le 1,5 milliard quantifié tient en mémoire (10,7 jetons/s) : mesurer avant de choisir.

# Choisir un modèle local sous contrainte de mémoire

**Mesures faites sur le poste (Ollama 0.34.3, processeur, GPU MX350 de 2 Go inutilisable pour ces
modèles), même invite de 791 jetons :**

| Modèle                         | Taille | Lecture de l'invite | Génération    |
| ------------------------------ | ------ | ------------------- | ------------- |
| `qwen2.5:3b-instruct-q4_K_M`   | 1,9 Go | 11 jetons/s         | 3,6 jetons/s  |
| `qwen2.5:1.5b-instruct-q4_K_M` | 986 Mo | 218 jetons/s        | 10,7 jetons/s |
| `qwen2.5:0.5b-instruct-q4_K_M` | 397 Mo | 53 jetons/s         | 11,2 jetons/s |

Le facteur vingt sur la lecture de l'invite entre le 3 milliards et le 1,5 milliard ne vient pas du
nombre de paramètres mais de la pagination : avec moins de 500 Mo de RAM libre, un modèle de 1,9 Go est
relu depuis le disque en permanence. Une première question en RAG avec ce modèle n'avait toujours pas
répondu après neuf minutes.

**Règles retenues.**

- Mesurer la vitesse réelle sur une invite représentative (avec les passages du RAG), pas sur
  « bonjour » : le coût est dominé par la lecture de l'invite.
- `keep_alive` long dans l'appel : sans lui, environ 30 s de rechargement du modèle par question.
- Borner `num_ctx` (4096) et `num_predict` (400) : la fenêtre par défaut réserve de la mémoire pour rien.
- Un modèle de 1,5 milliard n'émet pas d'appel d'outil fiable. Rendre la première recherche déterministe
  (les passages sont joints à la question) et ne laisser au modèle que les appels complémentaires.
- `curl http://localhost:11434/api/ps` donne la mémoire réellement occupée par le modèle chargé, à
  publier avec les mesures de latence.
