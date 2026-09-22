Avec moins de 1 Go de RAM libre, rendre en `--workers 1` : 12 s en 1080p30 prennent environ 1 min 30 à 2 min et réussissent.

- Chaque worker lance un Chrome (environ 256 Mo) ; `doctor` signale `Low memory` à 0,8 Go libre.
- Commande utilisée : `npx hyperframes render --fps 30 --crf 18 --workers 1 --output renders/intro.mp4`.
- Le mode de capture reporté est `screenshot capture · hardware gpu` ; le rendu fait une déduplication des images statiques (15 % réutilisées sur l'intro).
- Tailles obtenues sur un fond sombre à grille fine : CRF 22 = 0,85 Mo, CRF 18 = 1,8 Mo pour 12 s. CRF 18 reste bien sous 4 Mo, donc on garde la qualité.
- La sortie MP4 est déjà web friendly : H.264 High, yuv420p, atome `moov` avant `mdat` (faststart).
- WebM VP9 depuis le MP4 : `ffmpeg -i intro.mp4 -c:v libvpx-vp9 -crf 36 -b:v 0 -deadline good -cpu-used 2 -row-mt 1 -threads 2 -an intro.webm` donne 0,6 Mo mais prend près de 4 min.
