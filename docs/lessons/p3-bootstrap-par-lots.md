Un bootstrap naïf sur 15 000 valeurs et 2 000 rééchantillons alloue 240 Mo d'un coup : le découper en lots de 100 garde le même résultat pour quelques Mo.

# Bootstrap d'une médiane sur un poste à mémoire courte

- Piège : `rng.choice(valeurs, size=(2000, len(valeurs)))` crée une matrice de 2 000 x 15 000 flottants, soit
  240 Mo, pour un seul groupe. Avec dix groupes, le poste (moins de 1 Go libre) ne suit pas.
- Correctif : boucler par lots (`lot = 100`), concaténer les médianes obtenues. Le tirage reste identique en loi et
  la graine reste fixée (`np.random.default_rng(20260922)`), donc le résultat est reproductible.
- L'intervalle de confiance percentile (2,5 % et 97,5 %) suffit pour une médiane et se lit facilement dans une
  fiche : « +10,2 %, IC 95 % : +8,8 % à +11,6 % ».
- Utilité concrète : il donne le droit de ne pas conclure. Sur les maisons de Montpellier, l'intervalle de
  l'évolution 2021-2025 contient 0 ; la fiche le dit au lieu d'annoncer une hausse.
- Pour une évolution, rééchantillonner les deux années séparément et regarder la distribution du rapport, pas
  seulement les deux médianes.
