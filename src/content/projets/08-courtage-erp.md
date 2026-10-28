---
slug: courtage-erp
titre: 'Mini-ERP de courtage santé et prévoyance'
ordre: 8
categorie: 'ERP, flux financiers et pilotage'
famille: engineering
resume: 'Un courtier grossiste délégataire reçoit chaque mois des bordereaux, un relevé bancaire et des retours de prestations, et doit en tirer les encaissements, ses commissions et les reversements à ses apporteurs. Ce mini-ERP le fait sur un portefeuille simulé, refuse en entier tout fichier qui ne passe pas les contrôles, alimente un tableau de bord dont chaque mesure est comparée au SQL, pré-remplit les dossiers reçus par un modèle de langage sous validation humaine, et score chaque mois le risque de résiliation.'
statut: publie
motif: pipeline
stack:
  [
    'Python',
    'FastAPI',
    'PostgreSQL',
    'SQLAlchemy',
    'Alembic',
    'Apache Airflow',
    'dbt',
    'Power BI',
    'scikit-learn',
    'LightGBM',
    'API Claude',
    'Docker',
  ]
liens:
  github: 'https://github.com/yzasmin/courtage-erp'
  demo: 'https://courtage-erp-demo.onrender.com/'
teaser: 'video/projets/courtage-erp.mp4'
metriques:
  - { label: 'Lots refusés en entier, aucune ligne écrite', valeur: '9 sur 27' }
  - { label: 'Comparaisons entre mesures Power BI et SQL', valeur: '302' }
  - { label: 'Défauts réintroduits, tous détectés par les tests', valeur: '9 sur 9' }
  - { label: 'Tests passés', valeur: '255' }
---

## Contexte et problème

Un courtier grossiste délégataire conçoit des produits d'assurance santé et prévoyance, anime un réseau
d'apporteurs et gère, au nom des compagnies, des actes qui reviendraient normalement à l'assureur :
quittancement, encaissement, règlement de prestations. Ce que le cabinet a le droit de faire dépend d'une
convention de délégation, datée, produit par produit.

Chaque mois, trois courriers financiers arrivent : le bordereau de cotisations de chaque compagnie, le
relevé de la banque et le retour de prestations du concentrateur de tiers payant. Avant de facturer quoi
que ce soit, le cabinet doit savoir quelles cotisations appelées ont été encaissées, combien les compagnies
lui doivent en commission, et combien il reverse à chacun de ses apporteurs. Dans beaucoup de cabinets ce
rapprochement se fait encore à la main, et les erreurs se concentrent sur les lignes que personne ne relit :
un fichier chargé deux fois, une quittance hors période de garantie, un total qui ne tombe pas juste.

Un contrat, de son côté, change pendant sa vie : de formule, de tarif, d'apporteur. Pour calculer une
commission ou régler une prestation trois ans plus tard, il faut pouvoir dire dans quel état il était à une
date donnée.

J'ai donc construit le cœur de gestion, la chaîne qui contrôle et rapproche les flux, et le tableau de bord
qui en rend compte, sur un portefeuille entièrement simulé. J'y ai ajouté deux modules d'intelligence
artificielle : un agent qui lit les documents reçus et pré-remplit un dossier qu'un gestionnaire valide, et
une prédiction de résiliation scorée chaque mois. La recherche dans les conditions générales n'est pas faite.

## Données

Aucune donnée réelle. Le dépôt produit lui-même son jeu de démonstration, à graine fixe (20261019), avec un
historique arrêté au 30/09/2026. Chaque valeur qui ressemble à un paramètre d'assurance (taxes, taux de
commission, plafonds, formules) est une hypothèse de simulation, listée dans `docs/HYPOTHESES.md` et stockée
en base avec ses dates de validité, jamais écrite en dur dans le code.

| Entité                      | Lignes  | Source                                 |
| --------------------------- | ------- | -------------------------------------- |
| Entreprises souscriptrices  | 40      | `results/generation.json`              |
| Adhérents                   | 5 382   | `results/generation.json`              |
| Contrats                    | 5 737   | `results/generation.json`              |
| Avenants                    | 28 816  | `results/generation.json`              |
| Appels de cotisation        | 51 324  | `results/generation.json`              |
| Encaissements               | 37 154  | `results/generation.json`              |
| Prestations                 | 49 970  | `results/generation.json`              |
| Lignes empreintées au total | 178 658 | `results/reproductibilite_sqlite.json` |

Le courrier du mois est généré à part : 27 fichiers (bordereaux en CSV et en XLSX, relevés bancaires,
retours de prestations), avec un manifeste de vérité terrain qui liste les anomalies injectées, dont un fichier
redéposé tel quel, un total faux et un acte non délégué.

Une première version du jeu produisait un ratio de sinistres sur primes supérieur à 100 % sur toutes les
périodes (177,67 % en 2024), parce que le générateur ne reliait pas les prestations aux cotisations. Je l'ai
corrigé le 22/10 (décision D32 du dépôt) : les demandes de chaque contrat sont calibrées sur sa cotisation,
avec une échelle par branche et une consommation à queue lourde, et j'ai remesuré tout ce qui en dépendait.
Les prestations de 2020 à 2023 n'ont pas de cotisation en face, le quittancement simulé commençant le
01/10/2023 : aucun ratio ne se lit avant 2024.

## Approche

Cinq modules, chacun construit sur les précédents.

**Cœur ERP.** Un modèle de 33 tables, deux moteurs (PostgreSQL en production, SQLite pour développer et
tester) servis par le même code, des migrations Alembic qui montent et redescendent sans résidu. Chaque
changement d'un contrat est un avenant daté, et l'état à une date se reconstitue en relisant les avenants :
l'API dit de quel avenant provient la valeur retournée. La délégation de gestion est vérifiée avant toute
écriture qui engage la compagnie, avec trois périmètres différents dans le jeu, dont une convention arrivée
à échéance et non renouvelée. L'interface, en gabarits Jinja2 et HTMX, comprend la fiche contrat, la saisie,
l'écran des anomalies et la vue du réseau et des délégations.

**Flux financiers.** Une chaîne de neuf étapes, chacune lancée par une commande qui renvoie un code de
retour : détection des fichiers, archivage avec empreinte SHA-256, chargement, rapprochement, commissions,
bordereaux, rapport de détection, journal par lot, alerte. Huit contrôles bloquants s'appliquent avant
toute écriture (schéma, fichier déjà chargé, doublon de clé métier, contrat ou adhérent inconnu, total faux,
période hors garantie, acte non délégué) : une seule violation rejette le lot entier, et le fichier part
avec son rapport d'anomalies. Le
graphe Airflow n'importe aucun code métier, il appelle ces commandes. Le rapprochement se fait par
référence, puis par montant et date dans une fenêtre paramétrable, et conserve la méthode employée. Les
commissions se calculent au taux valide à la date d'échéance de l'appel.

**Pilotage.** Un modèle en étoile construit par dbt (52 modèles, 151 tests), qui ajoute les cotisations
acquises réparties au prorata des jours, un cliché mensuel du portefeuille et les mouvements d'affaires
nouvelles et de résiliations. Quatre indicateurs sont définis une fois, avec leurs pièges chiffrés, dans
`docs/kpi.md` : ratio de sinistres sur primes, taux de résiliation, production par apporteur, portefeuille
par branche. Un projet Power BI versionné en texte les affiche, et chaque mesure est comparée à la requête
SQL de contrôle correspondante.

![Les anomalies de rapprochement dans l'ERP : neuf lots refusés avec leur motif, puis les écarts de rapprochement](/images/projets/courtage-erp/anomalies-rapprochement.jpg)

![Le graphe Airflow de la chaîne des flux financiers, exécuté sur la pile Docker](/images/projets/courtage-erp/graphe-airflow.jpg)

**Agent de pré-remplissage.** Trente documents fictifs en PDF (demandes de remboursement, factures,
attestations d'employeur), dont huit portent une anomalie injectée : nom altéré, adhérent inexistant, total
faux, soins antérieurs à l'effet du contrat. Claude Haiku en extrait les champs dans un format structuré
imposé, revalidé à la réception. Le dossier est ensuite confronté au portefeuille : adhérent trouvé, nom
comparé, contrat rattaché, garantie en vigueur à la date des soins, total égal à la somme des lignes,
délégation de la compagnie. Il naît toujours « à valider » : seule la décision du gestionnaire crée la
prestation, et la base refuse une prestation liée à un dossier non validé. Chaque réponse du modèle est
enregistrée avec l'empreinte de la requête, ce qui rejoue l'évaluation sans clé ni appel payant.

![L'écran des dossiers pré-remplis : champs lus, contrôles et décision du gestionnaire](/images/projets/courtage-erp/dossiers-a-valider.png)

**Prédiction de résiliation.** Un modèle dbt produit une ligne par contrat et par 1er du mois, avec pour
cible une résiliation effective dans les douze mois. Un test dbt vérifie qu'aucune variable ne lit une
information postérieure à la date d'observation. L'apprentissage porte sur les 1er janvier 2023 et 2024, le
test sur le 1er janvier 2025. Une régression logistique est déployée, LightGBM sert de comparaison, et les
deux sont mesurés contre un plafond : la formule même du générateur, que nul modèle ne peut dépasser sur ces
données. Un graphe Airflow mensuel score le portefeuille, et la fiche adhérent affiche la probabilité avec
ses trois facteurs principaux.

![La fiche adhérent avec son score de résiliation et ses trois facteurs](/images/projets/courtage-erp/fiche-adherent-score.png)

## Choix techniques

| Choix                                                                     | Plutôt que                                    | Pourquoi                                                                                                                                                                                                                                                            |
| ------------------------------------------------------------------------- | --------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Rejet du lot entier dès qu'un contrôle bloquant échoue                    | Charger les lignes valides, signaler le reste | Un chargement à moitié fait se découvre des semaines plus tard et ne se défait pas proprement. Le fichier rejeté reste intact dans le dossier des rejets avec son rapport, qui est la pièce à renvoyer à l'émetteur                                                 |
| Historisation par avenants, état reconstitué à une date                   | Une table d'historique en parallèle           | L'avenant est déjà l'objet que manipule le métier : l'historique n'est tenu qu'à un endroit, et reconstituer un état revient à le relire. Un contrôle compare le cache porté par la table `contrat` à cette reconstitution, pour qu'ils ne divergent pas en silence |
| Montants en centimes entiers                                              | Nombres à virgule flottante                   | Un centime perdu sur un bordereau de commissions est une anomalie comptable, et l'entier se comporte à l'identique sur PostgreSQL et SQLite                                                                                                                         |
| Un graphe Airflow qui appelle des commandes, sans importer le code métier | Un graphe qui importe l'application           | Airflow contraint strictement ses dépendances et l'application utilise SQLAlchemy 2 : les mêler rend l'installation fragile. La chaîne se rejoue aussi à la main sur un poste sans Airflow (`courtage flux chaine`)                                                 |
| Commission au taux valide à la date d'échéance de l'appel                 | Taux en vigueur au jour du calcul             | Une revalorisation du barème réécrirait sinon des bordereaux déjà réglés                                                                                                                                                                                            |
| Contraintes de vérification en base, en plus du code                      | Validation applicative seule                  | Un ERP reçoit aussi des écritures par imports et par scripts de reprise. Cinq écritures SQL directes interdites sont refusées par la base sur les deux moteurs                                                                                                      |
| Projet Power BI en texte (PBIP) et comparaison de chaque mesure au SQL    | Fichier binaire et vérification à l'œil       | Un fichier texte se relit et se versionne. La comparaison au SQL vérifie chaque chiffre que le tableau de bord affiche                                                                                                                                              |
| Gabarits Jinja2 et HTMX servis par l'application                          | Application React séparée                     | Un écran de gestionnaire est une liste, une fiche et un formulaire. Aucune chaîne de compilation à installer, un seul processus à démarrer pour la démonstration                                                                                                    |

## Résultats et métriques

Chaque valeur vient d'une exécution enregistrée dans `results/`. L'intégration continue rejoue les preuves
sur SQLite et sur PostgreSQL et monte la pile Docker ; le dernier passage est réussi (exécution 40685118412). J'ai exécuté le graphe Airflow sur la pile Docker de
mon poste.

| Mesure                                             | Valeur                                                                                               | Source                                     |
| -------------------------------------------------- | ---------------------------------------------------------------------------------------------------- | ------------------------------------------ |
| Lots reçus, rejetés en entier                      | 27, dont 9 rejetés, 4 082 lignes écartées, 0 ligne écrite en base                                    | `results/flux_rejet_integral.json`         |
| Fichiers redéposés, état de la base                | 27 redéposés, empreinte d'état identique                                                             | `results/flux_idempotence.json`            |
| Contrôles bloquants contre les anomalies injectées | 9 sur 9 trouvées, aucun faux positif                                                                 | `results/flux_rapport_detection.json`      |
| Avertissements contre les anomalies injectées      | rappel 0,908, précision 0,649                                                                        | `results/flux_rapport_detection.json`      |
| Lignes examinées par le rapprochement              | 2 588 : 2 488 rapprochées, 34 partielles, 16 en écart, 24 sans appel, 26 ignorées (acte non délégué) | `results/flux_rapprochement.json`          |
| Commissions calculées, trois mois                  | 3 704, et 2 978 rétrocessions                                                                        | `results/flux_commissions.json`            |
| Bordereaux produits                                | 33, dont 9 de commissions et 24 de rétrocessions                                                     | `results/flux_bordereaux.json`             |
| Modèles dbt, tests, exposition                     | 52 modèles, 151 tests, 3 expositions : 199 réussites, 4 avertissements, 0 erreur                     | `results/dbt_build.txt`                    |
| Mesures Power BI contre SQL                        | 302 comparaisons, écart relatif maximal 2,9e-14                                                      | `results/concordance_dax_sql.csv`          |
| Migrations qui montent et redescendent             | 8 révisions, 33 tables, aucun résidu                                                                 | `results/migrations_postgresql.json`       |
| Extraction des 30 documents par Claude Haiku       | 96,7 % des champs exacts, 8 anomalies sur 8 signalées, aucun document sain bloqué                    | `results/ia_evaluation.json`               |
| Coût et latence de l'extraction                    | 0,000272 $ et 1 807 ms en moyenne par document, 0,0081 $ pour les 30                                 | `results/ia_evaluation.json`               |
| Prédiction de résiliation, test au 01/01/2025      | ROC-AUC 0,645, lift 2,22 sur le premier décile, plafond du générateur 0,662                          | `results/ml_resiliation.json`              |
| LightGBM, même test                                | ROC-AUC 0,645, lift 1,98 : pas mieux que la régression logistique                                    | `results/ml_resiliation.json`              |
| Génération reproductible                           | même empreinte sur SQLite et PostgreSQL, deux exécutions chacun                                      | `results/reproductibilite_postgresql.json` |
| Cohérence métier des données                       | 18 contrôles, tous conformes                                                                         | `results/coherence_metier.json`            |
| Défauts réintroduits, détectés par les tests       | 9 sur 9                                                                                              | `results/mutation.json`                    |
| Suite de tests                                     | 255 passés, 13 sautés                                                                                | `results/pytest.txt`                       |

Les quatre indicateurs, sur le jeu simulé (`results/kpi_sql.json`) :

| Indicateur                 | 2024    | 2025    | 2026, 9 mois                      |
| -------------------------- | ------- | ------- | --------------------------------- |
| Ratio sinistres sur primes | 76,38 % | 79,99 % | 72,07 %                           |
| Taux de résiliation        | 10,98 % | 11,67 % | 8,67 % (11,59 % ramené à l'année) |

La production de 2025 compte 834 affaires nouvelles apportées par dix apporteurs, et le portefeuille au
30/09/2026 compte 3 926 contrats, dont 36 suspendus.

![Le ratio de sinistres sur primes par exercice et par branche, tiré des mesures du tableau de bord](/images/projets/courtage-erp/ratio-sinistres-primes.png)

![La page de synthèse du tableau de bord, ouverte dans Power BI Desktop](/images/projets/courtage-erp/tableau-de-bord-power-bi.jpg)

Le résultat qui m'a le plus servi est un défaut : le premier `dbt build` complet a fait échouer deux tests
de sévérité erreur sur 1 184 appels, parce que le recalcul SQL des commissions ignorait la règle que le code
applique quand la compagnie encaisse elle-même. L'intégration continue n'exécute pas dbt, elle ne l'aurait
pas vu. La règle est désormais reprise dans le SQL et dans la comparaison.

![La lignée dbt d'une commission, de l'appel de cotisation au bordereau de rétrocession](/images/projets/courtage-erp/lineage-dbt-commissions.jpg)

## Impact métier

L'outil s'adresse au responsable de gestion d'un cabinet de courtage grossiste délégataire, et à la
direction qui lit ses chiffres. Il les aide à trancher deux questions : quel fichier renvoyer à son émetteur
avant tout traitement, et quel chiffre croire quand deux calculs d'un même indicateur divergent. Tous les
chiffres viennent d'un portefeuille simulé et ne décrivent aucun marché réel.

**Mesuré.** Sur les 27 fichiers du mois simulé, 9 sont refusés en entier et aucun n'a laissé de ligne en base,
ce que `results/flux_rejet_integral.json` vérifie lot par lot. Le ratio de sinistres sur primes de 2024 vaut
76,38 % rapporté aux produits dont le cabinet règle les prestations, et 54,25 % rapporté à tous les
produits : prévoyance comprise, qui n'a aucune prestation déléguée, l'indicateur change de plus de vingt points
(`results/kpi_sql.json`, requête `01b`). Avec une seule définition, recoupée en SQL, deux services qui citent
ce ratio parlent du même périmètre.

**Non démontré.** Aucune durée de rapprochement manuel n'a été chronométrée, et la durée de la chaîne n'est
pas enregistrée dans `results/` : je n'avance donc aucun temps gagné. Je n'avance aucun gain en euros non
plus. Les taux, les plafonds et les périmètres de délégation sont des hypothèses de simulation.

## Limites et pistes d'amélioration

La recherche dans les conditions générales (module 4b) n'est pas faite. L'extraction est mesurée sur trente
documents que j'ai générés, au texte propre : ni scan, ni écriture manuscrite. Les dix champs faux portent
tous sur l'émetteur, que le modèle complète d'un libellé de service ; je n'ai pas corrigé la vérité terrain
pour les faire passer. La prédiction de résiliation reste modeste (ROC-AUC 0,645) parce que le générateur
tire les résiliations au hasard autour de ses propres causes : le plafond calculé avec sa formule n'est que
de 0,662. Même dans le décile le plus risqué, 29 % des contrats seulement sont résiliés dans l'année : le
score sert à choisir qui un chargé de clientèle appelle en premier. Le
remboursement santé est simplifié : il s'applique aux frais présentés, sans base de la Sécurité sociale.

La démonstration en ligne ne montre que l'interface, sur une base SQLite fournie : la chaîne Airflow, dbt et
Power BI ne tournent que sur la pile Docker. Hébergée gratuitement, elle s'endort après quinze minutes sans
visite, et la première page met alors environ une minute à s'afficher. Comme l'application n'a pas
d'authentification, la base de la démonstration est remise à zéro à chaque redémarrage.

Le portefeuille est simulé : les signaux qu'il contient (saisonnalité, hausses du 1er janvier, résiliations
liées à l'ancienneté et au tarif) sont ceux que le générateur y a mis. Le rappel et la précision des contrôles
se mesurent contre des anomalies que j'ai injectées moi-même, donc ils disent que les contrôles font ce que
j'ai écrit qu'ils feraient, pas qu'ils attraperaient les anomalies d'un fichier réel. La précision de 0,649 des
avertissements vient surtout d'un code, la proximité du plafond annuel (288 signalements pour 98 anomalies
injectées), qui signale aussi des cas que le générateur n'avait pas marqués. Le contrôle des montants
atypiques laisse passer 41 anomalies sur 346.

Le bordereau de commissions que la compagnie adresse au cabinet n'est pas ingéré : quand elle encaisse
elle-même, le calcul retient la cotisation appelée hors taxe, sur 1 184 appels comptés à part. Le bordereau de
prestations émis vers la compagnie n'est pas modélisé. Le jeu couvre la santé individuelle, la santé
collective et la prévoyance, ni l'automobile ni les dommages.
La capture du graphe Airflow date d'avant la tâche d'alerte : le graphe actuel compte quatorze tâches, la
capture en montre treize. Le projet Power BI versionné porte un chemin d'exemple vers les données, à régénérer
après un clone.

La suite logique, dans l'ordre : ingérer le bordereau de commissions de la compagnie pour supprimer l'assiette
de repli, faire exécuter dbt par l'intégration continue, puis la recherche dans les conditions générales,
que je ne publierai qu'avec ses mesures.
