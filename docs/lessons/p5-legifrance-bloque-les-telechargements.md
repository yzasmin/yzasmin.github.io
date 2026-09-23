Légifrance renvoie 403 à tout téléchargement automatisé : passer par le portail RT-RE bâtiment (Etalab 2.0) et par AIDA, en citant l'URL Légifrance de référence.

# Récupérer des textes réglementaires français par script

- `curl` sur `legifrance.gouv.fr` renvoie 403, avec ou sans en-têtes de navigateur, y compris sur
  `circulaire.legifrance.gouv.fr`. Les miroirs commerciaux (pappers) renvoient 403 également.
- Le portail `rt-re-batiment.developpement-durable.gouv.fr` publie les versions consolidées en PDF
  (annexes de l'arrêté, annexe de l'article R. 172-4 du CCH) et indique « tous les contenus de ce site
  sont sous licence etalab-2.0 » : téléchargement direct, licence claire, à privilégier.
- La base AIDA de l'Ineris publie le texte consolidé des arrêtés en HTML, utile quand seul le portail
  ministériel manque ; le pied de page porte un copyright de site, pas sur le texte officiel lui-même.
  Noter la provenance et citer l'URL Légifrance comme référence juridique.
- Le portail précise que ses PDF sont un outil de documentation sans portée juridique : la source de
  droit reste le Journal officiel. Écrire cette phrase dans le README évite de laisser croire qu'un
  assistant fondé sur ces copies a valeur réglementaire.
- Enregistrer date de téléchargement, taille et empreinte SHA-256 de chaque fichier dans un manifeste
  commité : les textes consolidés changent plusieurs fois par an.
