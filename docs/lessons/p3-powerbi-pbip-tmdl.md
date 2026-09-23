Un projet Power BI se versionne en texte au format .pbip (modèle TMDL) ; il s'écrit à la main, mais chaque fichier de configuration doit porter le bon `$schema`, sinon Power BI refuse d'ouvrir sans expliquer davantage.

# Écrire un projet Power BI (.pbip) à la main

- **Structure minimale** : `Nom.pbip`, `Nom.SemanticModel/` (`definition.pbism`, `definition/database.tmdl`,
  `model.tmdl`, `relationships.tmdl`, `expressions.tmdl`, `tables/*.tmdl`) et `Nom.Report/`
  (`definition.pbir`, `report.json`). Le TMDL s'indente avec des tabulations.
- **Piège n° 1, le `$schema`.** Avec une URL inventée, Power BI affiche « Des problèmes ont été détectés » et
  attend exactement `https://developer.microsoft.com/json-schemas/fabric/pbip/pbipProperties/1.[0-9]+.[0-9]+/schema.json`
  pour le `.pbip`. Le message donne le motif attendu : le lire au lieu de deviner.
- **Piège n° 2, le chemin court.** Lancer `PBIDesktop.exe` avec un chemin 8.3 (`DVF-HE~1.PBI`) échoue :
  « n'a pas d'extension de fichier valide ». Passer le chemin long entre guillemets.
- **Piège n° 3, l'apostrophe dans un nom.** En TMDL, un identifiant entre apostrophes double l'apostrophe
  interne : `measure 'Volume d''affaires' =`. Sinon le fichier est illisible pour le moteur.
- **Première ouverture** : les tables sont vides et un bandeau demande une actualisation manuelle (obligatoire dès
  qu'il y a une table calculée). Cliquer **Accueil > Actualiser** ; `Ctrl+R` ne déclenche rien.
- **Chemins de fichiers** : Power BI n'accepte pas de chemin relatif. Passer par un paramètre de requête
  (`expression X = "..." meta [IsParameterQuery=true, Type="Text", ...]`) généré par script, et le documenter.
- **Générer le TMDL par script** (ici `scripts/construire_pbip.py`, qui lit `powerbi/mesures.dax`) évite d'avoir
  deux définitions des mesures dans le dépôt : le fichier DAX lisible reste la source unique.
