Un seuil de volumetrie calibre sur la mediane semble raisonnable et se revele faux des qu'on mesure la vraie distribution : sur 60 jours de parution du BODACC, il refusait dix journees parfaitement normales.

# Calibrer un controle de volumetrie sur ce que fait la source, pas sur ce qu'on imagine

## Le premier controle, ecrit de tete

```python
mediane = statistics.median(valeurs)
bas, haut = mediane / 4, mediane * 4
```

Raisonnement : la mediane resiste aux valeurs extremes, un facteur quatre laisse de la marge.
C'est le controle que j'ai ecrit d'abord, et il passait tous ses tests, parce que ses tests etaient
ecrits sur des nombres inventes autour de 55.

## La mesure, qui change tout

Distribution reelle des annonces BODACC de l'Herault, **60 jours de parution** du 24 juin au
21 septembre 2026, relevee par une seule requete d'agregation sur l'API :

```
1, 4, 9, 11, 11, 60, 77, 93, 101, 164, 181, 184, 218, 219, 222, 243, 278, 303, 304, 324,
324, 342, 354, 356, 357, 361, 365, 389, 401, 423, 427, 428, 438, 439, 440, 444, 464, 468,
471, 478, 495, 495, 512, 514, 525, 545, 565, 574, 580, 598, 634, 710, 723, 808, 820, 899,
950, 1003, 1012, 1046
```

Minimum **1**, maximum **1 046**, mediane **425**, cinquieme centile **9**.

La dispersion n'est pas du bruit, elle est **structurelle** : le bulletin ne publie pas les memes
familles d'avis tous les jours. Une journee ne contenant que des depots de comptes en compte une
centaine, une journee de procedures collectives en compte plusieurs centaines. Une journee a
80 annonces est aussi normale qu'une journee a 800.

Le seuil « mediane divisee par quatre » donne une borne basse a 106, et **refuse dix des soixante
journees**, toutes legitimes. En rejouant l'historique, trois des douze parutions traitees ont ete
bloquees a tort avant que la mesure ne soit faite.

## Le reflexe a ne pas avoir

Desserrer le facteur jusqu'a ce que tout passe. On obtient alors un controle qui ne refuse plus rien,
donc qui ne controle plus rien, et qui donne l'illusion inverse.

## Ce qui remplace le seuil : deux controles, pas un

**1. La completude, qui est exacte.** L'API Explore renvoie un `total_count` pour la requete du jour.
Le nombre de lignes ecrites en couche bronze doit lui etre egal, exactement. Ce controle ne suppose
rien sur le rythme de publication, il attrape une pagination interrompue ou une reponse tronquee, et
il est vrai ou faux sans zone grise. C'est lui qui fait le vrai travail.

```python
controle_completude_extraction(nb_ecrit=475, total_source=475)   # OK
controle_completude_extraction(nb_ecrit=400, total_source=475)   # ECHEC, ecart -75
```

**2. La volumetrie, calibree sur les centiles empiriques** de la meme fenetre de 90 jours, elargis
d'une marge (division par 2 en bas, multiplication par 1,5 en haut). Le controle ne refuse plus une
journee creuse normale ; il refuse une journee hors de tout ce que la source a produit en trois mois,
ce qui est le signal recherche. Il reste bloquant.

## Deux regles generales qui en sortent

1. **Un controle statistique se calibre sur la distribution mesuree, jamais sur une intuition.**
   Une demi-heure de mesure a change la conception. Sans elle, le pipeline aurait refuse une journee
   sur six en production, et quelqu'un aurait fini par desactiver le controle.
2. **Un controle exact vaut mieux qu'un controle statistique quand il existe.** La completude etait
   disponible depuis le debut : le `total_count` etait deja lu par l'extracteur pour paginer. Il
   suffisait de le publier comme un controle nomme au lieu de le laisser dans une exception interne.

## Corollaire : separer bloquant et alerte

- **Bloquant** : completude de l'extraction, unicite des identifiants, homogeneite de la partition,
  perimetre departemental, volumetrie, fraicheur de la source, conservation entre silver et gold.
  Ce sont des proprietes que le pipeline lui-meme doit garantir.
- **Alerte** : couverture des codes NAF, presence d'un SIREN. Ces deux-la dependent d'une API tierce.
  Sa panne doit degrader la richesse des indicateurs, pas empecher de publier les comptes par
  commune, qui n'en dependent pas.
