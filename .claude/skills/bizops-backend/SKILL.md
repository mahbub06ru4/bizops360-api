---
name: bizops-backend
description: >-
  BizOps 360 Laravel backend conventions — the layered path (Form Request → DTO →
  Action → Domain → Eloquent), modular monolith boundaries under app/Modules, multi-
  tenant isolation (tenant_id + global scope + Action-level tenant check + Policy),
  RBAC with spatie/laravel-permission, API Resources + /api/v1 + Sanctum, OpenAPI via
  scramble, and the Pest / PHPStan / Pint verification gate. Use whenever writing,
  reviewing, or debugging any PHP/Laravel/Filament code in this repo, or implementing
  any spec phase (docs/spec.md).
---

# BizOps 360 — backend engineering skill

Read `docs/spec.md` and `CLAUDE.md` first. This skill is the how-to that backs the
rules in CLAUDE.md.

## The layered path (non-negotiable)

```
Filament Resource / HTTP Controller   ← thin: no business logic
  → Form Request                      ← HTTP validation only
  → DTO (readonly, ::fromRequest())   ← stable application input
  → Action (single business use case) ← the ONLY place business rules run
  → Domain rules / value objects
  → Eloquent model / repository
  → PostgreSQL
```

- An Action is one class, one `handle()` / `__invoke()`, named for the business
  operation. It opens its own DB transaction when it writes more than one row.
- A Filament Resource's `handleRecordCreation` / actions call the Action. It never
  calls `Model::create()` for a real business operation.
- Controllers return `SomethingResource`, never the model.

## Module layout

```
app/Modules/<Name>/
├── Actions/
├── Data/            # DTOs
├── Domain/          # rules, enums, value objects
├── Models/
├── Policies/
├── Database/        # migrations, factories, seeders for this module
├── Http/            # Requests, Resources, Controllers, Filament resources
└── Providers/<Name>ServiceProvider.php
```

Cross-module: call `OtherModule\Actions\X`. Never `use OtherModule\Models\Y` for
writes. A shared read contract can expose a DTO.

## Multi-tenancy — four layers, all required

1. **Column**: every tenant-owned table has `tenant_id` (FK, indexed, not null).
2. **Global scope**: a `BelongsToTenant` trait adds a global scope filtering by the
   current tenant, and auto-fills `tenant_id` on create.
3. **Action check**: the Action resolves the current tenant from a `TenantContext`
   service and asserts any referenced records belong to it — the scope is a safety
   net, not the guarantee.
4. **Policy**: every `viewAny/view/create/update/delete` checks tenant + permission.

Tenant context is set by middleware from the authenticated user (web) or the Sanctum
token's tenant (API). Never trust a `tenant_id` from request input.

### Tenant tests (write these every phase)

- A user of tenant A gets 403/404 hitting tenant B's record by id (API + Filament).
- An Action rejects a related-model id that belongs to another tenant.
- A global-scope bypass attempt (`withoutGlobalScope`) is not reachable from any route.

## RBAC

`spatie/laravel-permission`. Permissions are per-tenant (team mode, `team_id =
tenant_id`). Roles: at least `owner`, `admin`, `manager`, `staff`. Seed a permission
per resource-action (`employee.view`, `employee.create`, …). Policies check
`$user->can('employee.update')` plus tenant.

## API

- All routes under `/api/v1`, `auth:sanctum` + `throttle`.
- Token abilities scope what a mobile token may do.
- Every response through an API Resource. Paginated lists use Resource collections.
- `dedoc/scramble` generates the OpenAPI doc — keep controller signatures + Resources
  typed so it stays accurate. Check `/docs/api` still renders after changes.

## Testing (Pest)

Per feature: feature/API test (happy + validation + auth), unit test for domain
rules, authorization test, tenant-isolation test. Use module factories. Every bug
fix adds a regression test named for the bug.

## Verification gate (before "done")

```
./vendor/bin/sail bin pint
./vendor/bin/sail bin phpstan analyse
./vendor/bin/sail artisan test
```

All green, plus OpenAPI generates. Then summarize changed files + output.

## Do not

Rewrite a whole module for a small change · modify released migrations · put logic in
Filament/controllers · expose Eloquent from APIs · add microservices, a message
broker, or realtime before the spec's phase for it · make Redis a source of truth.
