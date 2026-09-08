# BizOps 360 — Product & Engineering Specification (MVP Edition, 2026)

Multi-tenant Business Operations Management SaaS. Source of record: `docs/spec-source.docx`.

## 1. Core Vision

One common business platform + industry-specific modules. Instead of separate
software for a travel agency, a real-estate company, and a consultancy, BizOps 360 is
one core system with modules that switch on per industry.

**Target businesses:** travel agencies / tour operators; real-estate companies;
consultancy / service companies.

**Executive note:** the technology direction (Laravel 13, PostgreSQL, Redis,
Filament 5, Flutter, Docker) is not being replaced. This edition strengthens the
architecture around modular boundaries, explicit use cases, DTOs, API contracts,
automated tests, static analysis, CI, and tenant isolation — the goal is engineering
discipline, not a stack change.

## 2. Architecture Principle

Business logic must not live inside Filament Resources, controllers, Flutter widgets,
or API serialization code. Every interface routes through the same layered path:

```
Flutter / Filament
  → HTTP Controller / Form Request
  → DTO
  → Application Action / Use Case
  → Domain Business Rules
  → Infrastructure / Eloquent
  → PostgreSQL
```

Filament is the first interface built, not the foundation of the business logic.

## 3. Modular Monolith Structure

```
app/
├── Modules/
│   ├── Identity/
│   ├── Tenant/
│   ├── Organization/
│   ├── HR/
│   ├── Operations/
│   ├── CRM/
│   ├── Finance/
│   ├── Documents/
│   ├── Notifications/
│   └── Industry/
│       ├── Travel/
│       └── RealEstate/
├── Http/
│   ├── Controllers/
│   ├── Requests/
│   └── Resources/
└── Support/
```

## 4. Platform Structure

### 4.1 Organization Management
- Company, Branches, Departments, Designations
- Employees, Teams, Users
- Roles, Permissions, RBAC
- Multi-tenancy — every company's data stays strictly isolated

### 4.2 Employee / HR Management
- Employee profile: personal info, contact info, designation, department, joining
  date, salary, emergency contact, employment status
- Attendance: check-in / check-out, late, early leave, attendance history, monthly reports
- Leave: requests, approval / rejection, leave balance, leave types, holiday calendar
- Employee documents: NID/passport, contracts, certificates, other attachments

### 4.3 Task & Operations Management
- Tasks: create, assign to employee/team, priority, status, deadline
- Subtasks, comments, attachments, activity history
- Manager views: today's tasks, overdue tasks, employee workload, department performance

### 4.4 CRM
- Pipeline: New Lead → Contacted → Interested → Follow-up → Negotiation → Converted
- Lead and customer management, contacts, follow-ups, activities, notes, reminders
- Sales pipeline and customer history

### 4.5 Finance
- Income: customer payments, other income
- Expenses: office, employee, supplier, other expenses
- Sales/Invoices: invoice, payment, due amount, partial payment, refund, receivable
- Reports: income vs. expense, profit/loss, outstanding payments, customer dues, monthly reports

### 4.6 Document Management
- Centralized documents per customer (passport, visa docs, invoice, agreement) and
  per employee (contract, certificate, ID)
- Upload/download, categories, access permissions, expiry dates, activity history

## 5. Industry Modules

### 5.1 Travel Agency Module
- Customer/passenger profile, passport, passenger info, travel history
- Visa pipeline: Customer → Visa Case → Documents Required → Documents Collected →
  Submitted → Processing → Approved / Rejected
- Ticketing: PNR, passenger, airline, flight, fare, commission, payment, refund, ticket status

### 5.2 Real Estate Module
- Project management: projects, locations, developers, project info
- Property/unit: size, floor, price, availability, facing, parking, status
- Sales pipeline: Lead → Site Visit → Interested → Negotiation → Booking →
  Installment → Completed

## 6. Application Layer: Actions & DTOs

Avoid one large generic service per model. Prefer small, business-oriented Actions
that represent a meaningful use case — reserve this pattern for real business
operations, not trivial CRUD.

```
app/Actions/
├── Employees/{CreateEmployee,UpdateEmployee,TerminateEmployee}.php
├── Customers/{CreateCustomer,ConvertLead}.php
└── Invoices/{CreateInvoice,RecordPayment,RefundInvoice}.php
```

**Boundary rule:** Form Requests validate HTTP input → DTOs make application input
explicit and stable → Actions execute the use case → API Resources control response
serialization. Eloquent models are never exposed directly from public APIs.

## 7. API Design

- Version from day one under `/api/v1`.
- OpenAPI contract generated from code (`dedoc/scramble` or `laravel-openapi`), not a
  hand-maintained YAML.
- Endpoints: `/api/v1/auth/...`, `/employees`, `/customers`, `/tasks`, `/invoices`,
  `/payments`, `/documents`.

## 8. Flutter Mobile App

Fully separate from the Laravel implementation; communicates only through the
versioned REST API. State management: **GetX**.

```
Flutter
├── Presentation
├── Application / State Management (GetX)
├── Domain
├── Data/{API Client, DTO / Models, Repositories}
└── Core/{Networking, Errors, Configuration}
```

Planned employee/manager features: login, dashboard, attendance, leave requests,
task updates, CRM follow-ups, customer info, notifications, documents, expenses.

## 9. Notifications & Realtime

- Task, leave-approval, payment-reminder, follow-up, visa-deadline, document-expiry
  notifications.
- Realtime via Laravel Reverb or a hosted WebSocket service — added only once core
  workflows are stable.

## 10. Multi-Tenancy & Security

Shared PostgreSQL database with `tenant_id` isolation for the MVP — isolation must
not rely on a single global scope alone.

```
Authenticated User → Current Tenant Context → Authorization Policy →
Tenant-aware Query / Scope → Database
```

| Area | Approach |
|---|---|
| Authentication | Breeze / Fortify (web) + Sanctum (API tokens for Flutter) |
| Authorization | `spatie/laravel-permission` for RBAC + Policies on every tenant-owned resource |
| Tenant isolation | Tenant context + policy checks + tenant-aware scopes — verified with explicit cross-tenant access tests |
| Audit logs | Model-level activity logging on key business tables |
| File access | Authorization + signed / temporary URLs on document downloads |
| API security | Rate limiting, request validation, Sanctum token abilities |

## 11. Technology Stack

### 11.1 Core (Keep)
Laravel 13 / PHP 8.3+ · Filament 5 (initial admin/web) · REST `/api/v1` + OpenAPI
(strengthen) · Sanctum · PostgreSQL · Redis (cache/queue) · Flutter/Dart · Docker /
Laravel Sail (local) · Render (initial prod host) · Neon PostgreSQL (prod DB) ·
S3-compatible object storage.

### 11.2 Engineering Quality (Add)
Actions / Use Cases + Domain layer · Form Requests + DTOs · API Resources (never raw
Eloquent) · Pest/PHPUnit (feature, unit, authorization, tenant-isolation) ·
PHPStan/Larastan · Laravel Pint · GitHub Actions CI (Phase 0) · Sentry/APM (before
paying customers).

### 11.3 Later
React + TS + Inertia custom frontend · Next.js public site / portal · Laravel Cashier
(Stripe) or regional gateway (SSLCommerz/bKash) · SMS/WhatsApp Business API ·
transactional email via Postmark/Resend.

## 12. Testing, Static Analysis & CI/CD

Testing is part of implementation from Phase 0. Every business feature ships with
tests: feature/API, unit/domain, authorization + tenant-isolation (including explicit
cross-tenant access attempts), validation, and a regression test for every fixed bug.

```
php artisan test
vendor/bin/pint
vendor/bin/phpstan analyse
```

CI on every push/PR: install deps → static analysis → formatting check → tests →
migration/application checks → deploy when approved.

## 13. CLAUDE.md — AI Development Rules

Lives at repo root (`CLAUDE.md`). Stack; Interface → Application → Domain →
Infrastructure; the 11 rules; verification commands.

## 14. Claude Code Development Workflow

Requirement → understand existing module & patterns → inspect relevant code → plan
the smallest safe change → write/update tests → implement Action / Domain logic →
implement API / Filament / Flutter interface → run tests → run static analysis +
formatter → review tenant/security impact → summarize changed files and verification.

Do not rewrite an entire module when a focused change is sufficient.

## 15. What Not to Over-Engineer

No microservices; no Kubernetes for the MVP; no message broker unless a real async
integration requires it; Redis is not the source of truth for business data; no
custom admin panel before the product requires it; no complex DDD abstractions where
simple Laravel patterns suffice; no realtime features before core workflows are stable.

## 16. Development Phases

| Phase | Scope |
|---|---|
| **0 — Engineering Foundation** | Laravel 13, Docker, PostgreSQL, Redis, Filament, Sanctum, modular structure, tenancy foundation, RBAC, DTOs, Actions, API Resources, OpenAPI, tests, PHPStan, Pint, GitHub Actions, CLAUDE.md, seed data |
| **1 — Organization** | Company, Branch, Department, Designation, Employee, Users, Roles, Permissions |
| **2 — HR** | Attendance, Leave, Holidays, Employee documents |
| **3 — Operations** | Tasks, projects, teams, assignments, comments, attachments, notifications |
| **4 — CRM** | Leads, customers, contacts, pipeline, follow-ups, activities |
| **5 — Finance** | Income, expenses, invoices, payments, receivables, reports |
| **6 — Industry Module** | Travel or Real Estate — one vertical for the first commercial proof |
| **7 — Flutter** | Employee/manager mobile features and API integration |
| **8 — Production** | Deployment, backups, monitoring, security hardening, observability |
| **9 — Realtime & Billing** | Realtime notifications, subscriptions, tenant onboarding, SaaS analytics |

## 17. Deployment Path

| Stage | Where | What runs |
|---|---|---|
| Local development | Mac (Docker / Laravel Sail) | Laravel, PostgreSQL, Redis — containerized |
| Source control | GitHub | Private repo, CI on every push |
| Production app | Render | Laravel web service + queue worker |
| Production database | Neon | Managed PostgreSQL, same schema as local |

Local and production DBs are both PostgreSQL, so Mac → Render + Neon is a config
change, not a data-layer rewrite.

## 18. Production Readiness Checklist

Tenant isolation tested (incl. explicit cross-tenant attempts) · RBAC & Policies
tested · API auth & token abilities reviewed · rate limiting configured · validation
+ authorization on every write endpoint · signed/temporary URLs for private documents
· queue workers for long-running jobs · DB backup/restore verified · error monitoring
enabled · audit logging for important actions · secrets in env/secret management,
never source control · CI passes before deployment · API versioning policy documented.

## 19. Why This Project Is Worth Building

Demonstrates working knowledge of Flutter, Laravel, PostgreSQL, Redis, Docker, REST
API design, SaaS architecture, RBAC, multi-tenancy, and disciplined AI-assisted
development. Commercial end state: "One platform to manage your entire business" —
starting with a travel agency or real-estate company, expanding to more SME verticals.
