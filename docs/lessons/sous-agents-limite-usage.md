Au-delà de trois ou quatre sous-agents en parallèle, la limite d'usage de l'API coupe tout le monde en même temps : travailler par vagues.

# Paralléliser les sous-agents sans saturer la limite

**Constat.** Pendant la phase 2, six sous-agents lancés ensemble ont été interrompus cinq fois de suite
par la limite d'usage de la session, chacun en plein milieu d'une étape. Même chose avec cinq agents le
lendemain. Avec trois agents, aucune interruption.

**Ce qui se passe.** Les sous-agents consomment le même quota que la session principale. Six agents qui
lisent des dépôts, exécutent des tests et rédigent consomment en quelques minutes ce qu'un seul agent
consomme en une heure. La limite tombe alors pour tous, y compris la session principale.

**Ce qui marche.**

- Trois agents en parallèle au maximum, quatre si deux d'entre eux font surtout de la rédaction.
- Lancer par vagues : les agents proches de l'aboutissement d'abord, les gros chantiers ensuite.
- Les agents restent reprenables. Après une coupure, un message de reprise (« limite levée, reprends où
  tu t'étais arrêté, vérifie l'état des fichiers sur disque ») repart du point d'arrêt sans perdre le
  contexte. Rien n'est perdu, mais chaque coupure coûte un aller-retour.
- Demander à chaque agent de commiter au fil de l'eau plutôt qu'à la fin : une coupure laisse alors un
  dépôt cohérent.
- Pour les vérifications, un modèle plus léger suffit et économise le quota.

**Piège à éviter.** Deux agents qui écrivent dans les mêmes fichiers finissent par se marcher dessus.
Répartir par dossier (un dépôt par agent, ou les fiches pour l'un et les composants pour l'autre) et le
dire explicitement dans la consigne de chacun.
