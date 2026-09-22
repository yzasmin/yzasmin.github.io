/** Préfixe un chemin interne avec la base du site (utile si le site quitte la racine). */
export function url(path = ''): string {
  return `${import.meta.env.BASE_URL.replace(/\/$/, '')}/${path.replace(/^\//, '')}`;
}
