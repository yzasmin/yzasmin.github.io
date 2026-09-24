Le BODACC publie tous les mouvements d'entreprises en Licence Ouverte, mais l'API Explore plafonne la pagination a 10 000 lignes et livre ses champs imbriques en chaine JSON : il faut requeter par jour et par departement, et desencapsuler avant toute lecture.

# Ce que le BODACC contient vraiment, et comment le requeter

- Source : API Explore v2.1 d'Opendatasoft, jeu `annonces-commerciales`,
  `https://bodacc-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/annonces-commerciales/records`.
  Producteur : DILA (services du Premier ministre). Licence Ouverte (`FR-LO`) sur data.gouv.fr.
  Aucune cle d'API, aucun compte.
- Volumetrie constatee le 23/09/2026 : **50 760 501 annonces** au total, dont **1 086 441 pour l'Herault**
  (`where=numerodepartement='34'`). La parution du jour meme etait deja disponible : c'est un flux
  reellement quotidien, pas un stock rafraichi de temps en temps.
- Sept familles d'avis (`familleavis`) : `creation`, `immatriculation`, `modification`, `radiation`,
  `collective` (procedures collectives), `vente` (ventes et cessions), `dpc` (depots des comptes).
  Sur une seule journee, une famille peut etre absente : les procedures collectives ne paraissent pas
  tous les jours. Ne jamais deduire d'une famille absente qu'il y a un probleme de collecte.

## Trois pieges de l'API

1. **Les dates sont typees.** `where=dateparution='2026-09-22'` renvoie
   `IncompatibleTypesInComparisonFilter`. Il faut ecrire `dateparution=date'2026-09-22'`.
2. **La pagination est plafonnee a 10 000 lignes** (`limit` 100 au maximum, `offset` borne).
   Une requete d'un jour pour un departement passe largement dessous, une requete nationale non.
   Mieux vaut verifier `total_count` et lever explicitement que tronquer en silence.
3. **Les champs imbriques arrivent en chaine de caracteres, pas en objet.** `listepersonnes`,
   `jugement`, `acte`, `listeetablissements`, `modificationsgenerales` sont du JSON serialise dans une
   colonne texte, et le meme champ peut contenir un objet ou une liste d'objets selon l'annonce.

## Ce que la famille d'avis ne dit pas

- Un **transfert** ne porte pas de famille propre. Il se lit dans `acte.descriptif`
  (« immatriculation suite a transfert de l'etablissement principal hors ressort ») ou dans
  `modificationsgenerales.descriptif` (« Transfert du siege social a ... »). Compter les familles
  `creation` et `immatriculation` comme des creations surestime donc les ouvertures reelles.
- Une **liquidation par conversion** contient les mots « redressement » et « liquidation » dans le meme
  `complementJugement`. L'ordre des tests de classement decide du resultat : il faut tester la
  liquidation avant le redressement, sinon une entreprise liquidee est comptee en redressement.
- Le **montant d'une vente de fonds** n'est pas un champ. Il est dans du texte libre
  (« acquis par achat au prix stipule de 345000.00 euros »), avec des separateurs variables.
  Un montant illisible doit rester nul, jamais zero : zero est un prix, l'absence n'en est pas un.
- `registre` est une **liste qui repete la meme identite** sous deux formes (`"752461681"` puis
  `"752 461 681"`), et qui contient l'ancien puis le nouvel exploitant dans une vente. Dedoublonner
  sur les chiffres, et valider la cle de Luhn du SIREN avant de s'en servir comme identifiant.
