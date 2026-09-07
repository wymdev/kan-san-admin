# PostgreSQL migration

The current installation's data must be preserved. Never use `migrate:fresh` on the source or on a target containing real data.

## Completed local cutover — 7 September 2026

The application now uses PostgreSQL at `127.0.0.1:55432`, database `kan_san`, with the supplied `postgres` login. All 37 source data tables passed row-count and canonical-content verification before commit. The original MySQL database remains intact, and the previous environment is privately backed up at `storage/postgres-local/before-postgres.env`. This file contains secrets and must remain untracked.

After cutover, the single history request imported 20 draws, retaining all 25 original draw IDs and leaving 40 draws in total. The durable history-completion marker is set. The application is out of maintenance mode. PostgreSQL checks passed: 15 automated tests / 136 assertions, plus read-only screen checks against copied records. The frontend build and Blade compilation passed; browser visual review was unavailable in this session.

## Local server

The prepared local instance lives in ignored `storage/postgres-local`, listens on `127.0.0.1:55432`, and uses password authentication. Connection values are kept in `.env`; the source database remains intact. Start and stop it with:

```powershell
.\scripts\start-local-postgres.ps1
.\scripts\stop-local-postgres.ps1
```

This is a local development instance, not a Windows auto-start service. Keep its data directory and `.env` backed up. Other developers can install PostgreSQL normally and use their own connection settings.

## Rehearsal or hosted migration

1. Back up MySQL and the existing `.env`. Keep `DB_*` pointing to MySQL while preparing the target. Preserve `APP_KEY`, uploaded files, passwords and token configuration.
2. Set `PG_MIGRATION_*` in `.env` to a separate, empty PostgreSQL database. Set `PG_MIGRATION_SSLMODE=require` when the host requires TLS. Do not commit credentials.
3. Ensure PHP has `pdo_pgsql`. The Dockerfile now installs it alongside `pdo_mysql`.
4. Run the target migrations and read-only preflight:

```sh
php artisan config:clear
php artisan migrate --database=pgsql_migration
php artisan database:copy-to-postgres
```

5. Resolve any schema drift reported by preflight. Stop all source writers: put the application into maintenance mode and stop its queue workers and scheduler. Keep them stopped until cutover or rollback is complete. A consistent database snapshot alone does not include writes made after that snapshot.
6. Copy into the empty target:

```sh
php artisan database:copy-to-postgres --execute
```

The command uses a source read-only snapshot and a target transaction. It copies raw rows in foreign-key order, preserving IDs, hashes, tokens, timestamps and relationships. Generated columns are recomputed and verified, including Pulse's binary-hash-to-UUID representation. All tables except the migration bookkeeping table are copied, including queues and sessions. Serialized cache objects containing NUL bytes use Laravel's PostgreSQL base64 storage format; verification compares their original bytes without instantiating objects. Every row is compared through canonical SHA-256 digests and row counts before commit. Numeric, boolean, JSON and generated-hash representations are normalized for comparison. Row hashes are sorted independently of database collation. Target sequences are advanced after verification.

The command refuses nonempty target tables, missing source columns in the target, missing required target fields and cyclic dependencies. It never truncates the target or modifies source rows. Failed inserts and verification roll back copied rows. Sequence values can advance despite PostgreSQL transaction rollback; the next successful copy resets them.

7. Run application checks against PostgreSQL using the isolated test database, then verify the actual copied data on the main admin screens. Run read-only customer/purchase/history lookups and verify public batch links. Avoid sending real notifications during rehearsal.
8. Change `DB_CONNECTION=pgsql` and set `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_SSLMODE` to the verified target. Remove or replace an existing `DB_URL`, which otherwise overrides individual fields. Clear cached configuration, restart workers, then take the application out of maintenance mode.
9. Keep the source and backups. Before any writes reach PostgreSQL, rollback is restoring the old `.env` and restarting the app. After new PostgreSQL writes, reconcile those writes before rollback; switching back alone would lose them.

## SQL compatibility

`DatabaseSql` handles reporting periods, age, date parts, date distance and JSON text access. Date parameters remain bound. Weekly reporting uses ISO week-year so dates around New Year share a consistent label. PostgreSQL date formatting and date-part behavior are described in the [formatting reference](https://www.postgresql.org/docs/current/functions-formatting.html) and [date/time reference](https://www.postgresql.org/docs/current/functions-datetime.html).

Fresh PostgreSQL migrations use stored generated log columns and a check constraint for extended purchase statuses. Historical MySQL migrations remain executable. SQLite remains available for quick automated tests.

The text `prize_won` field is preserved exactly. Its legacy numeric aggregation remains compatible, but display labels are not a reliable monetary ledger; see the project overview.
