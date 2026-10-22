Les huit questions qu'un responsable technique d'éditeur pose sur ce projet, et les réponses fondées sur ce qui a réellement tourné.

# Entretien : mini-ERP de courtage santé et prévoyance

Dépôt : https://github.com/yzasmin/courtage-erp
Chaque chiffre cité ici se retrouve dans un fichier de `results/`. Les données sont simulées, à graine fixe
(20261019) : ce qui se démontre est la mécanique, pas un marché. Les modules 4 et 5 (agent de pré-remplissage,
prédiction de résiliation) ne sont pas faits, et je le dis avant qu'on me le demande.

## 1. Pourquoi des avenants plutôt qu'une table d'historique ?

L'avenant est l'objet que le gestionnaire manipule déjà : un contrat change de tarif, de formule,
d'apporteur, et chaque changement est un acte daté. Si cet acte porte l'historique, il n'y a qu'une seule
vérité. L'état à une date se reconstitue en relisant les avenants jusqu'à cette date, et l'endpoint
`GET /api/v1/contrats/{numero}/etat?a_la_date=...` dit de quel avenant vient chaque valeur, ce qui permet
de remonter une valeur contestée jusqu'à son acte. Sur le contrat `SI-2020-00056`, la cotisation est de
72,86 euros en mars 2022 et de 50,87 euros en août 2022, après un changement de formule.

Le risque d'un modèle à avenants est un cache, la valeur courante portée par la table `contrat`, qui diverge
de l'historique. Un contrôle de cohérence les compare, et il fait partie des 18 contrôles de
`results/coherence_metier.json`, tous conformes sur les deux moteurs. Le jeu compte 28 816 avenants pour
5 737 contrats, ce qui donne une idée de la profondeur d'historique que le modèle doit tenir.

Ce que je n'ai pas : une reprise d'un historique réel, avec ses trous et ses avenants saisis dans le
désordre. Le jeu simulé est propre par construction.

## 2. Comment la délégation de gestion est-elle modélisée, et que se passe-t-il quand un acte n'est pas délégué ?

Une convention de délégation relie une compagnie à un périmètre d'actes (affiliation, avenant, radiation,
quittancement, encaissement, gestion des prestations) avec une portée, totale ou partielle, et des dates de
validité. Le jeu pose trois périmètres différents : une compagnie qui délègue tout sauf les sinistres, une
qui garde l'encaissement, une qui garde les prestations. Il contient aussi une convention arrivée à échéance
le 30/06/2026 et non renouvelée, pour que le refus soit constatable.

La règle vit dans le service, pas dans l'interface : `verifie_acte_delegue` est appelé avant l'écriture, à la
souscription et à chaque avenant, et il lève une erreur qui nomme la convention et les actes délégués. L'API,
l'interface et un script d'import passent donc par le même refus. Dans les flux, l'acte non délégué est aussi
un contrôle bloquant : un bordereau de cotisations portant sur un produit dont la convention a expiré est
rejeté (c'est le cas du lot `COTISATIONS_LIT_202608` du jeu). Tests : `tests/test_delegation.py`.

Les périmètres du jeu sont des hypothèses. Je ne sais pas comment un éditeur représente ses conventions, et le
modèle n'a aucune raison de lui ressembler.

## 3. Pourquoi rejeter le lot entier plutôt que charger les lignes valides, et comment le prouvez-vous ?

Parce qu'un chargement partiel est le pire des états : la base contient des lignes dont personne ne sait si
elles sont complètes, et le corriger demande de défaire à la main ce qui a été écrit. Un lot qui viole une
règle bloquante (schéma, fichier déjà chargé par son empreinte, doublon de clé métier, contrat ou adhérent
inconnu, total différent du total déclaré, période hors garantie, acte non délégué) n'écrit rien. Le fichier
reste intact dans le dossier des rejets avec son rapport d'anomalies, qui est la pièce à renvoyer à
l'émetteur.

La preuve est de trois sortes.

**En base.** `results/flux_rejet_integral.json` vérifie lot par lot : 27 lots reçus, 9 rejetés, 4 082 lignes
écartées, aucun lot rejeté n'a écrit de ligne.

**Par rejeu.** `results/flux_idempotence.json` redépose les 27 fichiers : l'empreinte d'état de la base est
identique. Un fichier redéposé est reconnu par son empreinte.

**Par mutation.** `results/mutation.json` réintroduit 9 défauts dans le code, par exemple relâcher la borne du
contrôle de période, et exige que les tests en détectent chacun : 9 sur 9.

Les contrôles bloquants retrouvent 9 anomalies sur 9 injectées, sans faux positif. Cette mesure est
circulaire, et je le précise toujours : j'ai injecté les anomalies moi-même, donc elle dit que les contrôles
font ce que j'ai écrit, pas qu'ils attraperaient les anomalies de fichiers réels.

## 4. Quelle confiance accorder à un rapprochement par montant et par date ?

Une confiance de présomption, et le modèle le garde écrit. Le rapprochement cherche d'abord une
correspondance par référence, puis, pour ce qui reste, par montant et date dans une fenêtre paramétrable (15
jours dans le jeu). La méthode employée est conservée sur chaque ligne, et c'est ce que montre l'écran des
anomalies : le gestionnaire qui instruit un litige sait s'il relit une correspondance certaine ou une
présomption.

Sur le jeu, `results/flux_rapprochement.json` donne 2 588 lignes examinées : 2 488 rapprochées, 34 réglées
partiellement, 16 en écart, 24 sans appel correspondant, et 26 ignorées parce que l'acte n'est pas délégué. Par
méthode, 2 244 se font par référence et 294 par montant et date. Quatre statuts, pas deux, parce que « payé
partiellement » et « payé en trop » sont des situations de gestion différentes de « non trouvé ».

Ce que je n'ai pas testé : des libellés de virement réels, des virements groupés, ou une fenêtre calibrée sur
des délais d'encaissement observés. La fenêtre de 15 jours est un paramètre, pas une mesure.

## 5. Comment les commissions sont-elles calculées, et que faites-vous quand la compagnie encaisse elle-même ?

Au taux valide à la date d'échéance de l'appel de cotisation, pas à la date du calcul : sinon une
revalorisation du barème réécrirait des bordereaux déjà réglés. Les taux sont des lignes en base avec leurs
dates de validité. La chaîne a produit sur trois mois 3 704 commissions et 2 978 rétrocessions pour les
apporteurs (`results/flux_commissions.json`), et 33 bordereaux, dont 9 de commissions et 24 de rétrocessions
(`results/flux_bordereaux.json`).

Quand la compagnie encaisse elle-même, le cabinet est normalement commissionné sur le bordereau qu'elle lui
adresse. Je n'ingère pas ce bordereau, et j'ai tranché ainsi (décision D24) : commissionner sur la cotisation
appelée hors taxe, qui est la meilleure assiette observable, et compter ces cas à part pour qu'ils restent
rapprochables. Ils sont 1 184 sur 3 776 appels examinés. La première version écartait ces appels, ce qui
faisait disparaître 31 % de l'assiette sans motif lisible : c'est un défaut relevé par la vérification
indépendante du 21/10.

La limite est dite dans le README : tant que ce quatrième flux n'est pas ingéré, ces commissions sont une
estimation d'assiette, pas le montant que la compagnie reconnaît.

## 6. Pourquoi deux moteurs, SQLite et PostgreSQL, et comment prouvez-vous qu'ils ne divergent pas ?

SQLite pour développer et tester vite, sans conteneur, et pour qu'un relecteur sans Docker puisse exécuter la
suite. PostgreSQL pour la production, avec les contraintes de vérification et les types qu'on attend d'un
ERP. Le risque est évident : un code qui marche sur l'un et pas sur l'autre. Je le traite par un seul jeu de
modèles SQLAlchemy et en exécutant la même preuve sur les deux.

Les preuves sont enregistrées. Les migrations montent et redescendent sans résidu sur les deux moteurs (6
révisions, 31 tables). La génération de données donne la même empreinte SHA-256 sur les deux, octet pour octet.
Et la suite de tests tourne sur les deux dans l'intégration continue.

Elle m'a pris en défaut. Un test comparait un lot à `lots[1]`, lu sans `order by` : sur PostgreSQL l'ordre
des lignes est l'ordre physique, il a changé, et le test a échoué en intégration continue alors qu'il passait
sur mon poste. Le défaut était dans le test, pas dans le code, et j'ai corrigé en comparant des ensembles.
Autre leçon, consignée dans `docs/lessons/` : une connexion ouverte pour inspecter le schéma bloque un DDL
sur PostgreSQL, ce que SQLite ne fait pas.

Les contraintes de vérification vivent en base en plus du code, parce qu'un ERP reçoit aussi des écritures
par imports et par scripts de reprise : cinq écritures SQL directes interdites sont refusées par la base sur
les deux moteurs (`results/contraintes_sqlite.json`, `results/contraintes_postgresql.json`).

## 7. Airflow et dbt pour un ERP, n'est-ce pas lourd pour une petite équipe ?

Je ne le crois pas pour le premier, avec une réserve pour le second, et je préfère donner les mesures.

Le graphe Airflow n'importe aucun code métier : chaque tâche appelle une commande et lit son code de retour.
La chaîne se rejoue entièrement sans Airflow ni Docker avec `courtage flux chaine`, et la suite de tests
n'a pas besoin d'Airflow. Airflow est donc un ordonnanceur autour d'une chaîne qui existe sans lui, pas un
cadre dans lequel le métier vit. Il tourne en exécuteur local, sans Celery ni Redis.

dbt est isolé dans son propre groupe de dépendances parce qu'il en tire une cinquantaine dont l'application
n'a aucun besoin. Il apporte les tests de qualité, la documentation et le lineage, et le projet en compte 151.
Le prix est réel : l'intégration continue n'exécute pas dbt, il demande PostgreSQL et la chaîne de flux. Un
défaut de règle de commission dans un modèle dbt en est resté invisible jusqu'au premier `dbt build` complet.

Sur la mémoire, mesurée avec `docker stats` pile entière démarrée : environ 1 567 Mio, soit 40,8 % de la
machine virtuelle Docker de 3,75 Go. Le serveur web d'Airflow occupait à lui seul 706,6 Mio de sa limite de
768 Mio, ce qui l'exposait à une interruption au premier pic : j'ai porté sa limite à 1 024 Mio. L'ERP seul
et PostgreSQL tiennent dans environ 168 Mio. Une brique peut donc se prendre sans l'autre : le contrôle des
fichiers reçus sans Airflow, les indicateurs sans le reste.

## 8. Qu'est-ce qui a mal tourné, et que ne couvre pas le projet ?

Trois défauts m'ont le plus appris.

Le premier ratio de sinistres sur primes dépassait 100 % sur toutes les périodes (177,67 % en 2024). Le
générateur ne reliait pas les prestations aux cotisations, et il tirait les soins sur les quatre premières
années d'un contrat, si bien qu'un contrat de 2019 ne consommait plus rien en 2025. Je l'ai corrigé le 22/10
(décision D32), avec une échelle par branche et une consommation à queue lourde, puis j'ai remesuré tout ce
qui en dépendait : le ratio de 2024 passe à 76,38 %. L'empreinte du jeu a changé, et c'est voulu : une
empreinte restée identique aurait signifié que la correction n'avait rien produit.

Le deuxième : le recalcul SQL des commissions ignorait la règle D24 que le code applique, et deux tests dbt
de sévérité erreur échouaient sur 1 184 appels. Le troisième vient de la relecture par deux agents à contexte
vierge : une ligne contraire à un invariant faisait planter le chargement au lieu de rejeter le lot, et un
fichier illisible levait une exception plutôt qu'un rejet. Une chaîne qui s'arrête sur un fichier douteux au
lieu de le refuser est précisément ce que le projet voulait éviter.

Ce que le projet ne couvre pas : les modules 4 et 5, donc aucun modèle de langage ni aucune prédiction, et
aucune métrique de ce genre n'est publiée. Pas d'authentification. Pas d'automobile ni de dommages : le jeu
couvre la santé individuelle, la santé collective et la prévoyance, et les paramètres sont par produit dans
des tables, donc extensibles, mais je n'ai modélisé ni testé aucune autre branche. Pas de concentrateur de
tiers payant réel : les retours de prestations sont des fichiers simulés. Pas de volume de production : 5 737
contrats.

La suite que je ferais d'abord : ingérer le bordereau de commissions de la compagnie pour supprimer
l'assiette de repli, faire exécuter dbt par l'intégration continue, ajouter l'authentification, puis
seulement les modules d'intelligence artificielle, avec leurs mesures.
