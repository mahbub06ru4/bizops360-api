# Deploying BizOps 360 (Render + Neon + S3)

Matches spec §17 (Deployment Path). Local dev stays Docker/Sail on the Mac —
this doc is only about the first real deploy. `render.yaml` in the repo root
is the Render Blueprint; this doc is the setup around it that Render can't do
for you.

## What Render builds

`docker/production/Dockerfile` — one image, three roles selected by the
`CONTAINER_ROLE` env var:

- **`bizops360-api`** (web, `CONTAINER_ROLE=web`) — nginx + php-fpm via
  supervisord, listens on Render's `$PORT`, health-checked at `/up`. Runs
  `php artisan migrate --force` on every boot, before nginx starts serving —
  so a bad migration fails the deploy instead of going live half-broken.
- **`bizops360-api-worker`** (background worker, `CONTAINER_ROLE=worker`) —
  `php artisan queue:work --tries=3 --max-time=3600`. Render restarts the
  process if it exits; `--max-time` recycles it hourly to shed memory growth.
- **`bizops360-api-reverb`** (web, `CONTAINER_ROLE=reverb`) — `php artisan
  reverb:start`, the realtime WebSocket server (spec §9). Task/follow-up
  notifications broadcast here in addition to the `database` channel; the
  Flutter app (and any browser client) connects to this service's own URL,
  not the main API's. Needs its own public URL because clients hold a
  long-lived WebSocket connection to it directly.

Build it locally to sanity-check before you ever touch Render:

```bash
docker build -f docker/production/Dockerfile -t bizops360-api:prod-test .
docker run --rm -p 8080:8080 -e APP_KEY=base64:$(openssl rand -base64 32) \
  -e DB_CONNECTION=sqlite -e DB_DATABASE=/tmp/test.sqlite \
  -e CACHE_STORE=array -e SESSION_DRIVER=array -e QUEUE_CONNECTION=sync \
  bizops360-api:prod-test
curl http://localhost:8080/up   # expect 200
```

## One-time manual setup (you — needs accounts I can't create)

1. **GitHub**: nothing to do, the repo already exists.
2. **Neon** (neon.tech): create a project → a database → copy the pooled
   connection string (`postgresql://user:pass@host/db?sslmode=require`).
   That whole string is `DB_URL`.
3. **Render** (render.com):
   - New → **Blueprint** → connect the `bizops360-api` GitHub repo → it reads
     `render.yaml` and proposes the `bizops360-api` web service, the
     `bizops360-api-worker` background worker, and the `bizops360-api-reverb`
     realtime service.
   - Add a **Key Value** instance (Render's managed Redis) in the same
     region → copy its internal connection URL into `REDIS_URL` on the web
     service (the worker inherits it via `fromService`).
   - Fill in the env vars marked `sync: false` in `render.yaml` — Render's
     dashboard prompts for each on first deploy:
     - `APP_KEY` — generate once, **outside** the container:
       `php artisan key:generate --show` (run it in Sail locally) and paste
       the `base64:...` value. Never reuse the dev key.
     - `DB_URL` — the Neon string from step 2.
     - `AWS_ACCESS_KEY_ID` / `AWS_SECRET_ACCESS_KEY` / `AWS_DEFAULT_REGION` /
       `AWS_BUCKET` / `AWS_ENDPOINT` — from step 4.
     - `CORS_ALLOWED_ORIGINS` — the origin(s) that call the API from a
       browser (the Flutter web build's domain, and `/docs/api` if you want
       it browsable from somewhere other than the API's own origin). Leave
       unset only if nothing browser-based calls the API yet.
     - `SENTRY_LARAVEL_DSN` — from step 5, or leave blank to skip monitoring
       for now (the SDK no-ops without a DSN).
     - `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` — any values
       you generate (they're shared secrets between the API and the Reverb
       service, not a third-party credential — e.g. `openssl rand -hex 16`
       for each). The Reverb service and the worker inherit them via
       `fromService`.
   - After the first deploy, edit `bizops360-api`'s `REVERB_HOST` to that
     service's real Render URL if it differs from the `bizops360-api-reverb`
     default in `render.yaml`, and point the mobile app's Echo config at the
     same host.
4. **S3-compatible storage** — any of these work, pick one:
   - AWS S3 — standard, no `AWS_ENDPOINT` needed.
   - Cloudflare R2 / DigitalOcean Spaces — set `AWS_ENDPOINT` to the
     provider's S3-compatible endpoint and keep
     `AWS_USE_PATH_STYLE_ENDPOINT=true`.
   Create a **private** bucket — employee documents and task attachments are
   served only through signed URLs (see `CLAUDE.md` rule 8), never a public
   bucket URL.
5. **Sentry** (sentry.io, optional but recommended before real tenants): new
   project → PHP/Laravel → copy the DSN into `SENTRY_LARAVEL_DSN`.
6. Push to `main` (or click **Manual Deploy** the first time) — Render builds
   the Dockerfile and deploys both services.

## After the first deploy

- Confirm `https://<your-service>.onrender.com/up` returns `200`.
- Confirm `https://<your-service>.onrender.com/docs/api` renders the OpenAPI
  UI (restricted outside `local` env by Scramble's default gate — see
  `config/scramble.php` `'ui'` block if you want it open, e.g. behind a
  `permission:` middleware instead of removing the gate entirely).
- Run through `docs/ops/BACKUPS.md` once to prove restore actually works —
  don't wait for an incident to find out `pg_restore` fails.
- Walk `docs/spec.md` §18 (Production Readiness Checklist) — see the status
  table below.

## Production readiness checklist status (spec §18)

| Item | Status |
|---|---|
| Tenant isolation tested (incl. cross-tenant attempts) | ✅ automated, every module |
| RBAC & Policies tested | ✅ |
| API auth & token abilities reviewed | ✅ Sanctum, abilities derived from permissions |
| Rate limiting configured | ✅ `throttle:60,1` general, `throttle:6,1` auth |
| Validation + authorization on every write endpoint | ✅ |
| Signed/temporary URLs for private documents | ✅ employee documents, task attachments |
| Queue workers for long-running jobs | ✅ this deploy — nothing async yet queued by default, worker is ready |
| DB backup and restore verified | ⚠️ **you must run the BACKUPS.md restore drill once** |
| Error monitoring enabled | ⚠️ wired (Sentry), **needs a real DSN** to be "on" |
| Audit logging for important actions | ✅ `spatie/laravel-activitylog` on Invoice/Payment/Refund/Employee/LeaveRequest/Booking/VisaApplication/Lead/Customer/Subscription, tenant-scoped, `GET /api/v1/activity` |
| Secrets in env/secret management, never source control | ✅ `.env*` gitignored, Render env vars, nothing hardcoded |
| CI passes before deployment | ✅ GitHub Actions, required to stay green |
| API versioning policy documented | ✅ `/api/v1`, spec §7 |

The remaining ⚠️ rows are the honest gaps before onboarding a paying tenant:
run the `BACKUPS.md` restore drill for real (don't just trust that Neon PITR
exists), and put a real Sentry DSN in before the first paying tenant so
errors actually get reported instead of silently no-op'd.
