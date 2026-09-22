Mode d'emploi pour remplir une fiche projet (phase 2).

# Remplir une fiche projet

Chaque projet est un fichier de `src/content/projets/`. Tant que le corps du fichier est vide,
la page affiche les six sections du gabarit avec la mention « En cours de réalisation ».
Dès qu'il y a du texte sous l'en-tête, c'est ce texte qui s'affiche, et le sommaire se construit
à partir des titres `##`.

## En-tête (frontmatter)

```yaml
---
slug: challenge-kaggle # adresse de la page : /projets/challenge-kaggle/
titre: 'Challenge Kaggle'
ordre: 2 # position dans la grille
categorie: 'Deep learning multimodal'
famille: science # science | analyse | engineering | web (couleur)
resume: 'Une ou deux phrases, visibles sur la carte et en haut de la fiche.'
statut: publie # en-cours tant que la fiche n'est pas terminée
motif: scatter # poster | scatter | bars | pipeline | graph | histogram
stack: ['Python', 'PyTorch', 'PyTorch Lightning']
liens:
  github: 'https://github.com/yzasmin/...'
  demo: 'https://...'
  notebook: 'https://...'
teaser: 'video/projets/challenge-kaggle.mp4' # fichier dans public/, voir Portfolio/video/teaser-template
metriques:
  - { label: 'F1 macro sur le test', valeur: '0,87' }
  - { label: 'Classement final', valeur: '1re' }
---
```

Les liens absents s'affichent « À venir ». Les métriques apparaissent en tuiles sous le teaser.

## Corps (Markdown), à recopier sous l'en-tête

```markdown
## Contexte et problème

La question métier ou scientifique, pour qui, et pourquoi elle compte.

## Données

Sources, volume, période, qualité, nettoyage, biais identifiés.

## Approche

Les étapes, de l'exploration au modèle, au pipeline ou au tableau de bord.

## Choix techniques

| Choix   | Plutôt que  | Pourquoi                                        |
| ------- | ----------- | ----------------------------------------------- |
| PyTorch | TensorFlow  | Exemple : contrôle fin de la boucle, écosystème |
| DuckDB  | pandas seul | Exemple : requêtes SQL sur 10 Go en local       |

## Résultats et métriques

Métrique principale, référence (baseline), intervalle ou validation croisée, lecture métier.

## Limites et pistes d'amélioration

Ce que le projet ne démontre pas, les biais restants, la suite envisagée.
```

Pour une fiche en MDX (composants, graphiques), renommer le fichier en `.mdx`.
