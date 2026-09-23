Docker Desktop 4.36 refuse de démarrer tant qu'un socket périmé traîne dans `%LOCALAPPDATA%\Docker\run` : le moteur ne démarre jamais et l'erreur n'est visible que dans le journal.

# Docker Desktop bloqué par un socket périmé

**Symptôme.** `docker info` répond `open //./pipe/dockerDesktopLinuxEngine: The system cannot find the
file specified`, l'interface affiche une boîte d'erreur puis se ferme. Le processus `com.docker.backend`
apparaît quelques secondes puis disparaît.

**Diagnostic.** Dans `%LOCALAPPDATA%\Docker\log\host\com.docker.backend.exe.log` :

```
backend crashed ... running services: running OTel manager: removing stale socket:
remove <HOME>\AppData\Local\Docker\run\userAnalyticsOtlpHttp.sock: The file cannot be accessed by the system.
```

Le moteur WSL s'arrête juste avant (`engine linux/wsl stopped (running engine: waiting for the VM setup
to be ready: context canceled)`). L'échec est déterministe : trois lancements, trois fois la même erreur.

**Correction.** Supprimer le fichier `userAnalyticsOtlpHttp.sock` (socket AF_UNIX orphelin, laissé par un
arrêt brutal) puis relancer Docker Desktop. Un redémarrage de Windows produit le même effet.

**Règle.** Quand Docker Desktop se ferme sans message utile, lire le journal `com.docker.backend.exe.log`
avant de réinstaller quoi que ce soit : la cause est souvent un fichier résiduel, pas une configuration.
Prévoir, dans un projet qui dépend de Docker, une voie de repli qui n'en dépend pas (ici l'archivage brut
du flux en JSON Lines, qui a continué à collecter pendant que le moteur était en panne).

**Contournement retenu.** Un runner GitHub Actions `ubuntu-latest` fournit Docker et Docker Compose, avec
plus de mémoire que le poste. La pile complète y tourne dans une tâche dédiée : `docker compose up -d`,
rejeu de l'archive commitée, quelques minutes d'ingestion en direct pour mesurer la latence, `dbt build`,
`pytest`, export SQL des chiffres, artefact `results/`, puis `docker compose down`. Deux précautions :
paramétrer les limites mémoire du compose par variables (`${REDPANDA_MEM_LIMIT:-700m}`) pour desserrer en
CI sans casser l'exécution locale, et marquer les données rejouées (`is_replay`) pour qu'elles ne
contaminent pas les mesures de latence. Ce qui reste impossible ainsi : toute capture d'interface, un
runner n'ayant pas de navigateur.
