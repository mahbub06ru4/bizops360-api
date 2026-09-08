---
name: bizops-filament
description: >-
  BizOps 360 Filament 5 admin panel conventions — resources as a thin interface over
  Actions/DTOs (never business logic in Resource classes), tenant-scoped panels via
  spatie/laravel-permission team mode, per-resource Policy gating, form/table schemas
  that mirror Form Requests, custom pages that call Actions, and per-module resource
  registration. Use whenever creating or editing anything under app/Modules/*/Http/Filament
  or configuring a Filament panel in this repo.
---

# BizOps 360 — Filament skill

Filament is the *first* interface, not the home of business logic (spec §2). Read
`CLAUDE.md` and the `bizops-backend` skill first.

## Rules

1. A Resource's `create`/`edit` actions build a DTO from the form data and call the
   module Action. Do **not** rely on Filament's default `Model::create()` for real
   business operations (anything with side effects, related rows, or domain rules).
   Trivial reference tables (Designation, LeaveType) may use the default.
2. Form fields mirror the matching Form Request's rules — same field names, same
   validation — so web and API stay consistent.
3. Every Resource sets `canViewAny`/`can*` through the model Policy
   (`protected static bool $shouldCheckPolicies = true` behaviour) — no Resource is
   reachable without a permission.
4. Tenant scoping: the panel resolves `TenantContext` in a middleware; Eloquent
   queries are already tenant-scoped by `BelongsToTenant`, so Resource tables need no
   manual `tenant_id` filter — but relation selects (`Select::relationship`) must be
   constrained to the current tenant.
5. Pipeline UIs (CRM stages, visa pipeline, real-estate sales) are custom pages or
   `Filament\Actions` that call state-transition Actions
   (`AdvanceVisaCase`, `ConvertLead`) — never a raw status column edit.
6. Each module registers its own resources/pages in its ServiceProvider via a
   Filament plugin or `Panel::resources([...])`, keeping module boundaries intact.
7. File uploads for Documents use the private disk + the signed-URL download Action,
   not a public `FileUpload` URL.

## Layout

```
app/Modules/<Name>/Http/Filament/
├── Resources/<Thing>Resource.php
├── Resources/<Thing>Resource/Pages/
├── Pages/            # custom dashboards, pipeline boards
└── Widgets/          # manager views: today's tasks, overdue, workload
```

## Verify

Panel loads for each seeded role with only its permitted resources visible; a
manager of tenant A never sees tenant B rows. Covered by feature tests hitting
Filament pages (`livewire` test helpers) + the tenant-isolation suite.
