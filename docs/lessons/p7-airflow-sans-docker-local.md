Sur un poste sans moteur Docker, Airflow reste demontrable : garder le DAG vide de toute logique, executer `airflow dags test` dans un workflow GitHub Actions, et rendre les tests du graphe optionnels en local avec `pytest.importorskip`.

# Prouver Airflow quand Airflow ne peut pas tourner sur le poste

Deuxieme projet du portfolio a buter sur le meme obstacle (Docker Desktop hors service, voir
`p4-docker-desktop-socket-perime.md`). La reponse est la meme, affinee.

## 1. Le DAG ne contient aucune logique

Chaque tache appelle une fonction de `radar/pipeline.py` et rien d'autre. Consequences directes :

- le pipeline se rejoue **sans ordonnanceur** (`python -m radar.cli executer --jour 2026-09-22`) ;
- les 135 tests unitaires tournent sur un poste ou Airflow n'est meme pas installe ;
- ce que l'ordonnanceur apporte reste visible et nommable : planification, reprises, journal par
  tache, rejeu d'une date. Pas l'execution du code metier.

## 2. Les tests du graphe se sautent proprement la ou Airflow manque

```python
@pytest.fixture
def dag():
    pytest.importorskip("airflow", reason="Airflow n'est installe qu'en integration continue")
    ...
```

En local : 3 tests sautes, annonces comme tels. En integration continue, dans le conteneur Airflow :
ils s'executent vraiment. Un test saute et annonce vaut mieux qu'un test absent.

## 3. Ce qui est reellement execute en integration continue, et ce qui ne l'est pas

Une precision qui compte : depuis que le compte AWS existe, **les chiffres publies viennent d'une
execution sur le vrai compartiment S3**, lancee depuis le poste par la ligne de commande du projet.
L'integration continue, elle, reste sur LocalStack, et c'est volontaire : le workflow est public et
ne doit porter aucune cle reelle. Elle prouve ce qu'elle peut prouver, c'est-a-dire l'orchestration
elle-meme, et le README dit lequel des deux porte quoi.


Le workflow monte la pile complete avec `docker compose up -d --build`, attend que PostgreSQL,
LocalStack et le serveur web soient sains, puis enchaine : `airflow dags list`,
`airflow dags list-import-errors`, `airflow tasks list --tree`, les tests du graphe,
`airflow dags test <dag_id> <date>`, une **deuxieme** execution de la meme date pour prouver le rejeu,
et une preuve que les controles de qualite bloquent vraiment.

## 4. Deux pieges rencontres

- **Un volume `./results` monte dans le conteneur devient la propriete de l'utilisateur `airflow`
  (uid 50000)** et le runner ne peut plus y ecrire : `tee: Permission denied`. Le conteneur n'avait
  aucun besoin d'ecrire dans `results/` ; le montage a ete retire.
- **`terraform fmt -check | tee fichier` ne bloque jamais** : le code de sortie du tube est celui de
  `tee`. Sans `set -o pipefail`, le controle de format est decoratif. Le meme piege guette
  `pytest | tee`.

## 5. Un nom d'etape YAML ne peut pas contenir « : »

`- name: pytest (sans Airflow : les tests sont sautes)` donne
`mapping values are not allowed here`, et GitHub rejette le workflow entier **sans creer aucun job** :
le run apparait en echec a 0 seconde, sans journal. Valider le YAML avant de pousser
(`python -c "import yaml, sys; yaml.safe_load(open('.github/workflows/ci.yml'))"`) coute trois
secondes et evite un aller-retour a vide.
