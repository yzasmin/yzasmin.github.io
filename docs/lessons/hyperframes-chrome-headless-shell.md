Sur ce poste, le Chrome système bloque `hyperframes doctor` ; pointer HYPERFRAMES_BROWSER_PATH vers le chrome-headless-shell téléchargé.

- Symptôme : `doctor` affiche `Failed to run "C:\Program Files\Google\Chrome\Application\chrome.exe" --version (signal SIGKILL, ETIMEDOUT)`.
- `npx hyperframes browser ensure --force` a bien téléchargé `~/.cache/hyperframes/chrome/chrome-headless-shell/win64-152.0.7977.30/chrome-headless-shell-win64/chrome-headless-shell.exe`, mais la commande n'a jamais rendu la main (plus de 10 min, sortie vide) : vérifier le dossier plutôt que d'attendre.
- `chrome-headless-shell.exe --version` répond immédiatement (Chrome for Testing 152.0.7977.30).
- Définir `HYPERFRAMES_BROWSER_PATH` sur ce chemin avant `check`, `snapshot` et `render` : tout passe.
