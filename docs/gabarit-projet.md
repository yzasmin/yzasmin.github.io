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

## Impact métier

Pour qui c'est utile, quelle décision cela éclaire, deux ou trois chiffres avec leur statut.

## Limites et pistes d'amélioration

Ce que le projet ne démontre pas, les biais restants, la suite envisagée.
```

Pour une fiche en MDX (composants, graphiques), renommer le fichier en `.mdx`.

## La section « Impact métier », en détail

Elle est **obligatoire** dans chaque fiche, entre « Résultats et métriques » et « Limites et pistes
d'amélioration ». Elle existe pour un lecteur précis : le recruteur ou la recrutrice qui comprend ce qui a
été fait mais pas ce que cela ferait gagner à une entreprise. Six à douze lignes, pas plus ; si la fiche
devient trop longue, resserrer ailleurs plutôt qu'allonger ici.

Trois éléments, dans cet ordre :

1. **Pour qui**, une fonction précise et nommable : responsable d'agence immobilière, exploitant de réseau
   de vélos, chargé d'études, bureau d'études thermiques, service environnement d'une collectivité, équipe
   de recherche. Pas « les entreprises », pas « les décideurs ».
2. **Quelle décision cela éclaire**, en une phrase : fixer un prix de mise en vente, envoyer un camion de
   rééquilibrage, choisir le seuil sur lequel communiquer.
3. **Deux ou trois chiffres**, chacun accompagné de son statut.

### La règle mesuré contre estimé

C'est la règle la plus importante de la section. Chaque chiffre relève de l'un des deux statuts, et le
statut est écrit dans le texte.

| Statut     | Ce que c'est                                                                | Comment l'écrire                                                                                                                  |
| ---------- | --------------------------------------------------------------------------- | --------------------------------------------------------------------------------------------------------------------------------- |
| **Mesuré** | Le chiffre vient d'une exécution réelle enregistrée dans le dépôt du projet | Annoncer « Mesuré » et citer le fichier qui le porte : `results/latence.json`, `scripts/pipeline.py`, `results/seuils_oms_ue.csv` |
| **Estimé** | Le chiffre vient d'une hypothèse de travail, pas d'une exécution            | Annoncer « Estimé, au conditionnel », donner l'hypothèse et d'où elle vient, et rédiger au conditionnel                           |

Sont mesurables et font de bons chiffres : une durée d'exécution, un volume traité, une couverture
(communes, stations, espèces, documents), une fraîcheur, un délai de détection, un nombre de contrôles
automatiques, un taux d'erreur, un écart à une source de contrôle externe.

Forme acceptable pour une estimation : « en supposant qu'un analyste mette une demi-journée à refaire ce
croisement à la main, ce que la pratique courante d'un service d'études suggère, la chaîne ramènerait ce
délai à une minute et demie de calcul ».

**Interdit, sans exception** : un gain en euros, un pourcentage de productivité, un chiffre d'affaires,
une affirmation sur un client réel ou sur un déploiement qui n'a pas eu lieu. Un projet d'études ou un
travail d'équipe se présente comme tel : la section ne doit jamais laisser croire à une mise en production.

**Quand il n'y a rien de solide**, écrire moins mais juste. Un impact qualitatif honnête, ou une ligne
« Non démontré » qui dit ce que le projet ne prouve pas encore, valent mieux qu'un chiffre inventé.

### Vocabulaire

Un recruteur non technique doit tout comprendre sans dictionnaire. Chaque terme technique est traduit dans
la phrase qui le porte : « PM2,5 » devient « les particules fines PM2,5 », « p95 » devient « au 95e
centile », et un nom d'outil n'apparaît que s'il apporte une information au lecteur.
