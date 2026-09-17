# BizOps 360 — API

Multi-tenant Business Operations Management SaaS backend. Laravel 13 modular
monolith serving `/api/v1` to the [admin console](https://github.com/mahbub06ru4/bizops360-admin)
(web) and the Flutter mobile apps. Full product/engineering spec:
`docs/spec.md`.

## Stack

- Laravel 13, PHP 8.4+, PostgreSQL, Redis, Docker / Laravel Sail
- Auth: Fortify/Breeze (web) + Sanctum (API). RBAC: `spatie/laravel-permission`
  (teams mode — four fixed roles per tenant: owner/admin/manager/staff).
- OpenAPI generated from code (`dedoc/scramble`)
- Prod: Render (Docker web service) + Neon PostgreSQL

## Architecture

```
Interface (HTTP Controller) → Form Request → DTO → Action / Use Case → Domain → Infrastructure (Eloquent) → PostgreSQL
```

Modular monolith under `app/Modules/*` (Identity, Tenant, Organization, HR,
Operations, CRM, Finance, AdminUi, Industry/{Travel,RealEstate}).
Cross-module calls go through another module's Actions, never its Eloquent
models directly.

## Rules

1. Business logic must not live in controllers, jobs, or commands — only
   in Actions/Domain.
2. Use Form Requests for all HTTP validation.
3. Use readonly DTOs for application input; construct them from the Form
   Request.
4. Use small, business-named Actions (`CreateEmployee`, `RecordPayment`)
   — not one generic service per model, not for trivial CRUD.
5. Use API Resources for every API response. Never return raw Eloquent
   models or arrays from public APIs.
6. Every tenant-owned model has a `tenant_id`, a tenant global scope, AND
   its Action re-checks the current tenant context. A Policy guards every
   tenant-owned resource.
7. Enforce authorization via Policies on every read and write.
8. Private document downloads use signed/temporary URLs + authorization.
9. Do not modify already-released migrations — add a new one.
10. Add a regression test with every bug fix.

## The admin schema (`app/Modules/AdminUi`)

The single biggest consumer-facing piece of this API is
`GET /api/v1/admin/schema` (`AdminSchemaRegistry`) — it describes every
CRUD resource, workflow action, and dashboard chart the admin console
renders, so that frontend is a generic engine instead of one hand-written
screen per resource. See that class's docblock for the full schema shape
(`resources[].actions`, `resources[].detail.{relatedLists,embeddedLists,
activity}`, `mode`/`paginated`/`summaryEndpoint`/`link` flags, top-level
`dashboards` and `charts`).

**This registry does not replace Form Requests or Policies** — it mirrors
them for the frontend's benefit. Every field, permission string, and
endpoint in it must match a real Form Request/Policy/route; nothing here
is itself a source of authorization or validation truth.

When adding a new resource or workflow to `bizops360-api` that the admin
panel should expose:
1. Build it the normal way (Form Request → DTO → Action → Policy → API
   Resource → route) — the schema describes existing capability, it
   doesn't create it.
2. Add (or extend) the corresponding entry in `AdminSchemaRegistry`.
3. Add/extend the assertions in `tests/Feature/AdminUi/AdminSchemaApiTest.php`.

No frontend change is needed for the admin panel to pick it up.

## Commands (always via Sail)

```bash
./vendor/bin/sail up -d
./vendor/bin/sail artisan test
./vendor/bin/sail bin pint
./vendor/bin/sail bin phpstan analyse
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan db:seed   # demo tenant + 4 role accounts, password: password
./vendor/bin/sail psql              # raw SQL shell into the local Postgres
```

Never run `php` / `artisan` / `composer` directly on the host — always
through Sail.

## Deployment

`render.yaml` defines two Render web services (`bizops360-api`, and a
`bizops360-api-reverb` broadcasting service), Docker-built from
`docker/production/Dockerfile`. Database is Neon Postgres (`DB_URL`,
set manually in Render's dashboard — not in this repo). Migrations run
automatically on every deploy (`entrypoint.sh`); seeding a tenant is a
manual, one-time step via Render's Shell tab.

## Definition of done

- New/updated tests pass, including authorization + explicit cross-tenant
  access tests for any tenant-owned resource.
- Pint clean. PHPStan clean at the configured level.
- OpenAPI still generates.

## Verification

```bash
./vendor/bin/sail artisan test
./vendor/bin/sail bin pint
./vendor/bin/sail bin phpstan analyse
```
