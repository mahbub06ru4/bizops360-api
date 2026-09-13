# Deploying BizOps 360 (Render + Neon)

Matches spec §17 (Deployment Path). Local dev stays Docker/Sail on the Mac —
this doc is only about the first real deploy. `render.yaml` in the repo root
is the Render Blueprint; this doc is the setup around it that Render can't do
for you.

**Current default is the minimal path: Render + Neon only**, same shape as
other projects that got by on just those two. Redis and S3-compatible storage
are real upgrades (see §"Optional upgrades" below) but not required to get
live — `render.yaml`'s `bizops360-api` service runs on `CACHE_STORE=database`,
`SESSION_DRIVER=cookie`, `FILESYSTEM_DISK=local` until you add them. The one
thing that isn't optional: **Render's free plan has no background-worker
service type**, so `QUEUE_CONNECTION=sync` — queued jobs run inline in the
request instead of async. Fine at this scale; revisit when you're on a paid
plan (see the note at the bottom of `render.yaml`).

## What Render builds

`docker/production/Dockerfile` — one image, two roles selected by the
`CONTAINER_ROLE` env var:

- **`bizops360-api`** (web, `CONTAINER_ROLE=web`) — nginx + php-fpm via
  supervisord, listens on Render's `$PORT`, health-checked at `/up`. Runs
  `php artisan migrate --force` on every boot, before nginx starts serving —
  so a bad migration fails the deploy instead of going live half-broken.
  **This is also the step that fails loudest if `DB_URL`/`APP_KEY` are wrong
  or missing — check this service's deploy log first if it won't come up.**
- **`bizops360-api-reverb`** (web, `CONTAINER_ROLE=reverb`) — `php artisan
  reverb:start`, the realtime WebSocket server (spec §9). Task/follow-up
  notifications broadcast here in addition to the `database` channel; the
  Flutter app (and any browser client) connects to this service's own URL,
  not the main API's. Needs its own public URL because clients hold a
  long-lived WebSocket connection to it directly. Doesn't touch the database
  at all, so it comes up independently of Neon/`DB_URL` being correct.

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
   That whole string is `DB_URL`. If the connection specifically fails with
   an auth/SSL error (not "host not found"), Neon's default string sometimes
   includes `&channel_binding=require`, which some PDO/pgsql builds choke on
   — drop that parameter and keep just `?sslmode=require`.
3. **Render** (render.com):
   - New → **Blueprint** → connect the `bizops360-api` GitHub repo → it reads
     `render.yaml` and proposes the `bizops360-api` web service and the
     `bizops360-api-reverb` realtime service.
   - Fill in the env vars marked `sync: false` in `render.yaml` — Render's
     dashboard prompts for each on first deploy:
     - `APP_KEY` — generate once, **outside** the container:
       `./vendor/bin/sail artisan key:generate --show` and paste the
       `base64:...` value. Never reuse the dev key.
     - `DB_URL` — the Neon string from step 2.
     - `CORS_ALLOWED_ORIGINS` — the origin(s) that call the API from a
       browser (the Flutter web build's domain, and `/docs/api` if you want
       it browsable from somewhere other than the API's own origin). `*` is
       fine temporarily; tighten before real use.
     - `SENTRY_LARAVEL_DSN` — leave blank to skip monitoring for now (the SDK
       no-ops without a DSN).
     - `REVERB_APP_ID` / `REVERB_APP_KEY` / `REVERB_APP_SECRET` — any values
       you generate (they're shared secrets between the API and the Reverb
       service, not a third-party credential — e.g. `openssl rand -hex 16`
       for each). The Reverb service inherits them via `fromService`.
   - After the first deploy, edit `bizops360-api`'s `REVERB_HOST` to that
     service's real Render URL if it differs from the `bizops360-api-reverb`
     default in `render.yaml`, and point the mobile app's Echo config at the
     same host.
4. Push to `main` (or click **Manual Deploy** the first time) — Render builds
   the Dockerfile and deploys both services.

## Optional upgrades (add later, not needed to go live)

- **Redis** (`CACHE_STORE=redis`, `SESSION_DRIVER=redis`, `REDIS_URL`) —
  Render's own **Key Value** now requires a paid plan; **Upstash**
  (upstash.com, free, no card) is a drop-in alternative — create a Redis
  database, copy its `rediss://...` URL into `REDIS_URL`.
- **S3-compatible storage** (`FILESYSTEM_DISK=s3` + `AWS_*`) — needed once you
  want employee-document/task-attachment uploads to survive a redeploy
  (Render's own disk is wiped on every deploy). Any of AWS S3, Cloudflare R2,
  or DigitalOcean Spaces work — set `AWS_ENDPOINT` for a non-AWS provider and
  keep `AWS_USE_PATH_STYLE_ENDPOINT=true`. Create a **private** bucket —
  documents are served only through signed URLs (see `CLAUDE.md` rule 8),
  never a public bucket URL.
- **Sentry** (sentry.io) — new project → PHP/Laravel → paste the DSN into
  `SENTRY_LARAVEL_DSN`. Recommended before real tenants, not before.
- **A real background worker** — needs a paid Render plan (`type: worker`
  isn't available on free). Re-add it to `render.yaml` per the comment at the
  bottom of that file once you're ready to pay.

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
| Queue workers for long-running jobs | ⚠️ `QUEUE_CONNECTION=sync` on the free plan — jobs run inline, not async. Add Redis + a paid worker service (see "Optional upgrades") before anything queues real work |
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
