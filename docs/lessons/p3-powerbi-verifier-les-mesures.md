Les mesures DAX d'un rapport ouvert dans Power BI Desktop s'interrogent de l'extérieur : le moteur local écoute sur un port TCP et le client ADOMD livré avec Power BI suffit pour rejouer des requêtes et comparer au SQL.

# Contrôler des mesures DAX depuis un script

- Power BI Desktop lance un moteur Analysis Services (`msmdsrv.exe`). Son port se retrouve sans fichier de
  configuration : `Get-NetTCPConnection -OwningProcess (Get-Process msmdsrv).Id` (état `Listen`, `127.0.0.1`).
- Le client se charge depuis l'installation Power BI :
  `Add-Type -Path "<InstallLocation>\bin\Microsoft.PowerBI.AdomdClient.dll"`. Attention, le namespace n'est pas
  celui du nom de fichier : la classe est `Microsoft.AnalysisServices.AdomdClient.AdomdConnection`.
  Le fournisseur OLE DB `MSOLAP` n'est, lui, pas enregistré par la version Microsoft Store.
- Remplir la table avec `AdomdDataAdapter.Fill` plutôt que `DataTable.Load` : `Load` applique des contraintes et
  échoue dès qu'une mesure renvoie BLANK. Penser aussi à fermer le lecteur avant la requête suivante.
- Les nombres reviennent au format de la culture (virgule décimale) : lire les CSV avec `decimal=","`.
- Résultat concret sur le projet DVF : 129 comparaisons entre les mesures évaluées par Power BI et le même calcul
  en SQL (DuckDB), 15 mesures, 10 contextes de filtre, aucune différence au-delà de l'arrondi des flottants
  (écart relatif maximal 4,8e-14).
- Un écart de contexte n'est pas un écart de calcul : sur un total toutes années confondues, une mesure
  « évolution » qui utilise `MAX(dim_date[annee])` compare la valeur globale à l'année précédente. Le chiffre est
  correct, la lecture n'a pas de sens : le documenter plutôt que le masquer.
