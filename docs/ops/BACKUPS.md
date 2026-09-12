# Backup & restore

Neon is the production database (spec §17). It gives point-in-time recovery
(PITR) automatically, but "the provider has a feature" and "we've proven we
can restore from it" are different claims — do the drill below at least once
before real tenant data exists, and again whenever the schema changes a lot.

## What Neon gives you automatically

- **PITR**: every Neon project can restore to any point within its retention
  window (7 days on the Free plan, longer on paid plans — check the Neon
  dashboard for the project's actual window).
- Restoring via PITR in the Neon console creates a **new branch** at that
  point in time; it does not silently overwrite the live database. Point the
  app at the new branch's connection string only after you've verified it.

## Manual logical backup (belt-and-suspenders)

Run this on a schedule (a Render **Cron Job** hitting a small artisan command,
or from your own machine) against the Neon connection string:

```bash
pg_dump "$DB_URL" -Fc -f "bizops360-$(date +%Y%m%d-%H%M).dump"
```

Store the dump somewhere other than the S3 bucket the app itself writes to
(a separate bucket/prefix, or download it off-site) — a backup that lives
next to the thing it backs up doesn't survive that thing being deleted.

## Restore drill (do this once, write down what actually happened)

1. Create a throwaway Neon branch (or a local Postgres) to restore into —
   never restore over the live database as a "test."
2. Restore the dump:
   ```bash
   pg_restore -d "$TEST_DB_URL" --clean --if-exists "bizops360-<timestamp>.dump"
   ```
3. Point a local `.env` at `$TEST_DB_URL` and run:
   ```bash
   php artisan migrate:status   # confirms the schema matches what the app expects
   php artisan tinker --execute="echo \App\Models\User::count();"
   ```
4. Confirm row counts look sane for the tables that matter most (`tenants`,
   `users`, `invoices`, `bookings`). If they don't, the backup/restore process
   itself is broken — better to find that now than during an incident.
5. Tear down the throwaway branch/database.

## What's NOT backed up by any of the above

- **S3 bucket contents** (employee documents, task attachments). Turn on
  your storage provider's own versioning/replication for the bucket — this
  is provider-side configuration, not something Laravel or this repo does.
- Redis — it's cache/queue only (spec §15: never the source of truth for
  business data), so it's fine for it to be empty after a restore.
