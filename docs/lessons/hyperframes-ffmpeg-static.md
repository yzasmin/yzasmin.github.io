Sans FFmpeg système, HyperFrames fonctionne avec ffmpeg-static et ffprobe-static ajoutés au PATH de la seule commande.

- Dans `Portfolio/video/` : `npm i -D ffmpeg-static ffprobe-static hyperframes`.
- Binaires : `node_modules/ffmpeg-static/ffmpeg.exe` et `node_modules/ffprobe-static/bin/win32/x64/ffprobe.exe`.
- PowerShell : `$env:PATH = "$v\node_modules\ffmpeg-static;$v\node_modules\ffprobe-static\bin\win32\x64;" + $env:PATH` dans la même commande que le rendu.
- Git Bash : `export PATH="$V/node_modules/ffmpeg-static:$V/node_modules/ffprobe-static/bin/win32/x64:$PATH"`.
- `npx hyperframes doctor` confirme alors FFmpeg 6.1.1 et FFprobe 4.0.2 (versions des paquets npm).
- Deux formes de chemin cohabitent sous Git Bash, et les confondre coûte un rendu raté :
  - dans la variable `PATH`, la forme POSIX `/c/Users/...` est obligatoire, car `:` y sert de séparateur :
    un `C:/Users/...` est coupé en deux et FFmpeg reste introuvable (« FFmpeg not found », rendu arrêté en 12 s) ;
  - en argument passé à un .exe Windows, c'est l'inverse : `C:/Users/...` ou un chemin relatif, jamais `/c/...`.
- Piège Git Bash : les .exe Windows ne comprennent pas les chemins `/c/Users/...` passés en argument ; utiliser `C:/Users/...` ou des chemins relatifs.
  Cela vaut pour tous les binaires Windows, pas seulement ffmpeg : dans `video/teaser-projet/render.sh`, `git -C "$src/.."`
  et `python fichier.json` ont d'abord échoué avec « No such file or directory » sur un chemin `/c/...`. Correction appliquée :
  construire le dossier de base avec `pwd -W` (qui rend `C:/Users/...`) plutôt qu'avec `pwd`.
- ffmpeg-static inclut libx264 et libvpx-vp9 : il suffit aussi pour extraire un poster (`-ss 8 -frames:v 1 -q:v 3`) et produire un WebM VP9.
