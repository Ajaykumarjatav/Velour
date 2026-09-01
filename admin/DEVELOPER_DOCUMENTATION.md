# Developer documentation

**Velour (Vellor)** — salon management SaaS.

## Start here

👉 **[docs/README.md](docs/README.md)** — full handbook index

### Quick links

| Topic | Document |
|-------|----------|
| Install & run locally | [docs/01-getting-started.md](docs/01-getting-started.md) |
| Architecture & tenancy | [docs/02-architecture.md](docs/02-architecture.md) |
| Database & models | [docs/03-database-and-models.md](docs/03-database-and-models.md) |
| Routes (web + API) | [docs/04-routes-web-and-api.md](docs/04-routes-web-and-api.md) |
| Business logic / services | [docs/05-services-and-business-logic.md](docs/05-services-and-business-logic.md) |
| Blade & React frontend | [docs/06-frontend-and-views.md](docs/06-frontend-and-views.md) |
| Jobs & commands | [docs/07-jobs-commands-and-notifications.md](docs/07-jobs-commands-and-notifications.md) |
| Feature modules | [docs/modules/README.md](docs/modules/README.md) |
| **All class methods (generated)** | [docs/reference/CODE_REFERENCE.md](docs/reference/CODE_REFERENCE.md) |
| Permissions | [docs/reference/PERMISSIONS.md](docs/reference/PERMISSIONS.md) |
| Environment variables | [docs/reference/ENVIRONMENT.md](docs/reference/ENVIRONMENT.md) |

## Regenerate method index

After changing `app/`:

```bash
php scripts/generate-code-reference.php
```

## Legacy docs

Booking-specific guides remain at repo root: `INDEX.md`, `BOOKING_*.md`. Prefer `docs/` for system-wide documentation.
