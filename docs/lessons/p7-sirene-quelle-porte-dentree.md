Trois portes existent vers SIRENE et une seule convient a un flux quotidien sur un poste de 8 Go : l'API Recherche d'entreprises, sans cle, 7 requetes par seconde, a condition de n'interroger que les SIREN du jour.

# SIRENE : stock mensuel, API authentifiee, ou API ouverte

Mesures faites le 23/09/2026 avant d'ecrire une ligne de code.

| Porte d'entree | Ce que c'est | Verdict |
| --- | --- | --- |
| **Fichiers stock** sur data.gouv.fr (`lov2`, mensuel) | `StockEtablissement` en Parquet : **2 210 114 710 octets**, soit 2,21 Go, plus 0,71 Go pour `StockUniteLegale` | Ecarte : mensuel, donc incapable de porter un radar quotidien, et 2,2 Go a telecharger et filtrer sur un poste de 8 Go a chaque rafraichissement |
| **API SIRENE 3.11 de l'INSEE** (`api.insee.fr/api-sirene/3.11`) | Acces complet, y compris les variables non diffusibles | Ecarte pour l'instant : **HTTP 401** sans jeton, un compte INSEE et une cle sont necessaires. A reprendre le jour ou la cle existe |
| **API Recherche d'entreprises** (`recherche-entreprises.api.gouv.fr`) | Donnees SIRENE et RNE, **sans cle**, **7 requetes par seconde et par adresse IP** (30 par ASN) | Retenue |

## Ce que l'API ouverte ne sait pas faire, et ce que cela impose

- Elle **plafonne la pagination a 10 000 resultats**. Une requete `?departement=34` renvoie
  `total_results: 10000` quel que soit le nombre reel d'etablissements du departement : c'est un
  plafond, pas un comptage. Il est donc impossible de reconstituer le stock des etablissements de
  l'Herault par cette porte.
- La consequence est une decision d'architecture, pas un contournement : **le flux porte la liste,
  le referentiel ne fait qu'enrichir**. On part des annonces BODACC du jour, on en extrait les SIREN,
  et on ne demande a l'API que ces SIREN-la. Quelques centaines d'appels par jour, sous la limite de
  taux, contre 2,2 Go telecharges tous les mois.
- Les **entreprises non diffusibles sont absentes** de l'API. Un SIREN introuvable est donc un
  resultat normal et non une erreur : l'enrichissement doit degrader, pas echouer.
- Le code NAF n'arrive que par cet enrichissement. Si l'API tombe, les comptes par commune restent
  justes et seule la ventilation par secteur devient inconnue : c'est pourquoi le controle de
  couverture NAF est une alerte, et le controle de rapprochement des communes un blocage.

## Le referentiel geographique est ailleurs, et il est gratuit

`geo.api.gouv.fr/departements/34/communes` renvoie les **341 communes** de l'Herault avec code INSEE,
codes postaux, population et centroide, en 46 880 octets. C'est ce referentiel qui permet de refuser
un rapprochement douteux au lieu de deviner une commune.
