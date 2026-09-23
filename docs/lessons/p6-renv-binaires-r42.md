Avec R 4.2 sur Windows, pointer renv vers un instantané daté de Posit Package Manager : CRAN ne fournit plus de binaires pour les versions anciennes et tout compilerait.

# renv et binaires Windows pour R 4.2

**Contexte.** Le poste dispose de R 4.2.2 (l'installation d'une version récente par winget a échoué) et
n'a pas de Rtools utilisable pour compiler des paquets. CRAN ne publie des binaires Windows que pour la
version courante et la précédente : `install.packages()` bascule alors sur les sources et échoue sur
les paquets compilés (`data.table`, `stringi`, `car`...).

**Solution.** Utiliser un instantané daté de Posit Package Manager, qui conserve les binaires des
anciennes versions de R :

```r
# .Rprofile du projet, avant source("renv/activate.R")
options(repos = c(CRAN = "https://packagemanager.posit.co/cran/2024-04-01"))
```

```r
renv::init(bare = TRUE, restart = FALSE)
renv::install(c("data.table", "ggplot2", "naniar", "rstatix", "car"), type = "binary", prompt = FALSE)
renv::snapshot(prompt = FALSE)
```

106 paquets se sont installés en 3,5 minutes, sans compilation.

**Piège du lockfile.** `renv::snapshot()` inscrit dans `renv.lock` le dépôt actif au moment de
l'installation de renv lui-même, souvent `https://cloud.r-project.org`. Un `renv::restore()` sur une
autre machine chercherait alors les versions figées sur un CRAN qui ne les a plus. Vérifier et corriger
le bloc `R.Repositories` du `renv.lock` pour qu'il pointe vers l'instantané daté.

**Vérifier l'existence de l'instantané** avant de le choisir :
`curl -sI https://packagemanager.posit.co/cran/2024-04-01/bin/windows/contrib/4.2/PACKAGES`.
