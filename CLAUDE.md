# BizOps 360

Multi-tenant Business Operations Management SaaS. Full product/engineering spec: `docs/spec.md`.
Read `docs/spec.md` at the start of every phase before planning.

## Stack

- Laravel 13, PHP 8.4+, PostgreSQL, Redis, Filament 5, Flutter (GetX), Docker / Laravel Sail
- Auth: Fortify/Breeze (web) + Sanctum (API). RBAC: `spatie/laravel-permission`.
- OpenAPI generated from code (`dedoc/scramble`). Prod: Render + Neon + S3-compatible storage.

## Architecture

`Interface (Filament / HTTP Controller) → Form Request → DTO → Action / Use Case → Domain → Infrastructure (Eloquent) → PostgreSQL`

Modular monolith under `app/Modules/*` (Identity, Tenant, Organization, HR, Operations,
CRM, Finance, Documents, Notifications, Industry/{Travel,RealEstate}). Cross-module
calls go through another module's Actions, never its Eloquent models directly.

## Rules

1. Business logic must not live in Filament Resources, controllers, jobs, or commands — only in Actions/Domain.
2. Use Form Requests for all HTTP validation.
3. Use readonly DTOs for application input; construct them from the Form Request.
4. Use small, business-named Actions (`CreateEmployee`, `RecordPayment`) — not one generic service per model, not for trivial CRUD.
5. Use API Resources for every API response. Never return raw Eloquent models or arrays from public APIs.
6. Every tenant-owned model has a `tenant_id`, a tenant global scope, AND its Action re-checks the current tenant context. A Policy guards every tenant-owned resource.
7. Enforce authorization via Policies on every read and write.
8. Private document downloads use signed/temporary URLs + authorization.
9. Do not modify already-released migrations — add a new one.
10. Add a regression test with every bug fix.
11. Run all three verification commands before calling a task done.

## Commands (always via Sail)

```
./vendor/bin/sail artisan test
./vendor/bin/sail bin pint
./vendor/bin/sail bin phpstan analyse
```

Never run `php` / `artisan` / `composer` directly on the host — always through Sail.
Migrations: `./vendor/bin/sail artisan migrate`. New module code follows existing
Phase 0 conventions; inspect a sibling module first and match it.

## Definition of done

- New/updated tests pass, including authorization + explicit cross-tenant access tests for any tenant-owned resource.
- Pint clean. PHPStan clean at the configured level.
- OpenAPI still generates.
- Summary of changed files + the verification command output.

## Verification

php artisan test
vendor/bin/pint
vendor/bin/phpstan analyse
