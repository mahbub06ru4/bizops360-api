---
name: bizops-devops
description: >-
  BizOps 360 infrastructure & delivery — Laravel Sail (Postgres + Redis) for local
  dev on the Mac, GitHub Actions CI (pint --test → phpstan → pest → migrate) on every
  push/PR, and production on Render (web service + queue worker) with Neon PostgreSQL
  and S3-compatible storage. Use for anything touching docker-compose, Sail, .env,
  the CI workflow, deployment config, queue workers, backups, secrets, or the Mac↔
  Windows repo split.
---

# BizOps 360 — devops skill

Read spec §11, §17, §18.

## Local (Mac)

- Everything runs in Sail containers: `app`, `pgsql`, `redis`. No host PHP/Postgres.
- Commands go through Sail: `./vendor/bin/sail artisan …`, `./vendor/bin/sail bin pint`.
- `.env`: `DB_CONNECTION=pgsql`, `DB_HOST=pgsql`, `REDIS_HOST=redis`,
  `QUEUE_CONNECTION=redis`, `CACHE_STORE=redis`, `FILESYSTEM_DISK=s3`.
- Never commit `.env`. Keep `.env.example` current with every new key.
- Queue worker locally: `./vendor/bin/sail artisan queue:work`.

## CI — `.github/workflows/ci.yml` (both repos)

API workflow, on `push` + `pull_request`:

```
services: postgres:16, redis:7
steps:
  - checkout
  - setup-php 8.3, extensions, coverage none
  - composer install --no-interaction --prefer-dist
  - cp .env.example .env && php artisan key:generate
  - vendor/bin/pint --test
  - vendor/bin/phpstan analyse --no-progress
  - php artisan migrate --force
  - php artisan test
  - php artisan scramble:export   # OpenAPI still generates
```

Mobile workflow: `flutter analyze` → `dart format --set-exit-if-changed .` →
`flutter test`. Branch protection: CI green required before merge.

## Production (Render + Neon)

- **Web service**: Laravel app, `php artisan config:cache route:cache view:cache` on
  build, `php artisan migrate --force` on deploy.
- **Background worker**: separate Render service running `queue:work --tries=3`.
- **DB**: Neon PostgreSQL — same schema as local, so it's a `DATABASE_URL` change.
  Enable Neon's branching for preview environments if useful.
- **Storage**: S3-compatible bucket; private by default; downloads via signed URLs.
- **Secrets**: Render env groups / GitHub Actions secrets only. Never in source.
- **Monitoring**: Sentry DSN wired before onboarding paying tenants.
- **Backups**: verify Neon PITR + a scripted `pg_dump` restore test before Phase 8 sign-off.

## Mac ↔ Windows

`bizops360-api` is developed on the Mac (Docker). `bizops360-mobile` on the Windows
laptop. Sync only through GitHub. The mobile app reaches the Mac API in dev via LAN
IP or a `cloudflared` tunnel, set as `API_BASE_URL` — never hard-coded.

## Do not

Kubernetes, a message broker, or multi-region for the MVP. Redis is cache/queue
only, never the source of truth.
