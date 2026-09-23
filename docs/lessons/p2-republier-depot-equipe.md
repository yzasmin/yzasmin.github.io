Republier un dépôt d'équipe : garder la licence d'origine, vérifier ce que montrent vraiment les sorties, et traduire les chemins de serveur en variables d'environnement.

# Republier le dépôt d'un projet d'équipe

**Licence.** Le dépôt d'origine portait une licence MIT au nom d'un seul membre (« Copyright (c) 2026 Guilhem »).
Elle est conservée telle quelle : c'est elle qui autorise la republication. Le README crédite chaque membre avec
son rôle, et le tableau des rôles vient de la présentation, pas d'une supposition sur qui a écrit quelle ligne.
Dans un dépôt partagé sans historique git, on décrit le contenu des dossiers (« le dossier `yasmina/` contient... »)
plutôt que d'attribuer la paternité d'un fichier.

**Vérifier les sorties avant de les réutiliser.** Les PNG d'explicabilité semblaient être de vraies explications.
En ouvrant le GradCAM, les images d'entrée sont du bruit uniforme : elles viennent du chargeur factice
(`make_dummy_loader`, tenseurs aléatoires), pas des données. Regarder l'image avant de la publier comme résultat.

**Chemins de serveur.** Remplacés par des variables d'environnement avec valeur par défaut relative, par un script
de remplacement plutôt qu'à la main : `Path("/home/grpX/...")` devient
`Path(os.environ.get("GLC_DATA_DIR", "data")) / "..."`. Piège : le remplacement introduit `os.environ` dans des
fichiers qui n'importaient pas `os` ; le script signale les fichiers concernés et `python -m py_compile` sur tout
l'arbre confirme que rien n'est cassé.

**Test de fumée sans poids pré-entraînés.** Pour instancier un modèle qui charge EfficientNet-B3 ImageNet sans
télécharger 50 Mo, on remplace les constructeurs torchvision dans le test :
`monkeypatch.setattr(tvm, "efficientnet_b3", lambda *a, _f=original, **k: _f(weights=None))`. Le code du modèle
fait son import à l'intérieur de `__init__`, donc il lit bien l'attribut remplacé. Six architectures testées en
3 min 24 s sur CPU avec moins de 1 Go de RAM libre (batch de 2).

**Deux scores pour un même résultat.** Une soutenance annonçait 0,23389 et le tableau final 0,20227. Sans fichier
qui tranche, on publie les deux et on formule l'écart comme une hypothèse (classement public contre privé), sans
choisir le chiffre le plus flatteur.
