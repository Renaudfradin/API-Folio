# API Folio — Collection Bruno

Collection Git-native des endpoints publics de l'API (`/api/*`).

## Prérequis

- [Bruno](https://www.usebruno.com/) (application desktop) pour l'usage interactif
- API locale démarrée : `./vendor/bin/sail up` (port `8001` par défaut)

## Ouvrir la collection

1. Lancer Bruno
2. **Open Collection** → sélectionner le dossier `bruno/` à la racine du projet
3. Choisir l'environnement **Local** ou **Production** dans la barre d'environnements

## Environnements

| Environnement | baseUrl | Usage |
|---|---|---|
| Local | `http://localhost:8001` | Développement avec Sail |
| Production | `https://api-folio.up.railway.app` | API déployée |
| CI | `http://127.0.0.1:8000` | GitHub Actions uniquement |

### Variables de slug (endpoints `show`)

Les requêtes de détail utilisent des slugs configurables :

- `articleSlug`, `cameraSlug`, `categorySlug`, `photographySlug`, `projectSlug`

En local/CI, exécutez le seeder Bruno pour créer des enregistrements de test :

```bash
./vendor/bin/sail artisan db:seed --class=BrunoSeeder
```

Slugs par défaut : `bruno-test-article`, `bruno-test-camera`, etc.

## Structure

```
bruno/
├── General/          # Home, Health
├── Articles/         # list + show
├── Cameras/
├── Categories/
├── Experiences/      # list uniquement (pas de route show)
├── Photographies/
└── Projects/
```

## CLI

```bash
npm install
npm run bruno:run        # environnement Local
npm run bruno:run:ci     # environnement CI (rapport JUnit)
```

## Notes

- Tous les endpoints sont **GET publics**, sans authentification (rate limit : 60 req/min)
- `/api/health` est documenté ici mais absent de la spec OpenAPI Swagger
- `/api/experience/{slug}` est documenté dans Swagger mais **non implémenté** dans les routes Laravel
- Ne pas committer de secrets dans les fichiers d'environnement Bruno
