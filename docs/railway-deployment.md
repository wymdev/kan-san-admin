# Railway deployment

This project uses its root Dockerfile: Node 22 builds the assets, PHP 8.4 runs the app, and Supervisor starts Nginx, PHP-FPM and the queue worker. You do not need a separate build command or web start command.

## 1. Prepare the database before the first application deployment

Create a PostgreSQL service in the same Railway project/environment. For the current local PostgreSQL 18 backup, use a PostgreSQL 18 target. Check the database service version before restoring; do not change the major version of an existing database container in place.

If an existing production database has newer records, migrate that source instead of replacing it with this local snapshot. See [the MySQL migration guide](postgresql-migration.md). Keep its app, workers and scheduler paused while making the final copy.

For a new Railway database receiving the current local data, stop local application writers during the final dump and keep them stopped until cutover. From PowerShell in this project, export a custom archive (the password is prompted):

```powershell
& .\storage\postgres-local\pgsql\bin\pg_dump.exe -h 127.0.0.1 -p 55432 -U postgres -d kan_san -W -Fc -f .\storage\postgres-local\railway.dump
```

In the database service, obtain the public TCP proxy host/port and database/user values from its connection settings. Restore into the **empty** target before deploying the app, replacing the uppercase placeholders below. Do not use the private `railway.internal` host from your computer.

```powershell
& .\storage\postgres-local\pgsql\bin\pg_restore.exe -h PUBLIC_PROXY_HOST -p PUBLIC_PROXY_PORT -U DATABASE_USER -d DATABASE_NAME -W --no-owner --no-privileges --single-transaction --exit-on-error .\storage\postgres-local\railway.dump
```

Check the exit code and stop if restore fails. No `--clean` or schema reset is needed. Keep the dump private. This transfers the schema, migration records, original IDs and history-completion marker, so the 20-draw API import is not repeated. Compare source/target table counts and key customer/purchase lookups before accepting writes on Railway.

## 2. Configure the application service

Connect the repository containing these changes. Set the root directory to the project root and use Dockerfile detection. Set these **application service Variables** (replace placeholders):

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_KEY=YOUR_EXISTING_APP_KEY
APP_URL=https://YOUR_APP_DOMAIN
LOG_CHANNEL=stderr
LOG_LEVEL=info

DB_CONNECTION=pgsql
DB_URL=${{Postgres.DATABASE_URL}}

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

LOTTERY_RESULTS_BASE_URL=https://xn--t3cmiit.com
```

`Postgres` must match the actual database service name. Use Railway's reference-variable selector if it differs. The app reads `DB_URL`; setting only `DATABASE_URL` is insufficient. Remove stale MySQL `DB_*`, local `PG_MIGRATION_*`, and RapidAPI variables from the app service after restoring. Retain the existing mobile `API_KEY`, mail/Brevo settings, OCR key and optional Expo token where used. Preserve the existing `APP_KEY` rather than generating a new one.

| App service setting | Value |
| --- | --- |
| Builder | Dockerfile |
| Custom build command | Leave empty |
| Custom start command | Leave empty; use Docker ENTRYPOINT |
| Pre-deploy command | Leave empty; startup already runs migrations |
| Healthcheck path | `/up` |
| Replicas | 1 for this upload-volume setup |
| Public networking | Generate domain, then set `APP_URL` to its HTTPS URL |
| Volume mount path | `/var/www/html/storage/app` |

The volume preserves uploaded files across redeploys. Transfer the existing `storage/app/public` and `storage/app/private` contents into the matching directories on that volume. Railway's volume file browser/CLI can upload files. A new mounted volume is not populated from the Docker image. Restart the app after the initial transfer so startup applies file ownership. Do not mount over the whole project or transfer `storage/postgres-local` into the app service.

Startup requires `APP_KEY`, prepares volume directories/permissions, runs `migrate --force` and `storage:link`, caches config/routes/views, then starts the web server and worker. Docker context exclusions keep local credentials, database files and uploads out of the image. No results API request runs during deployment.

## 3. Enable scheduled tasks

Create one additional service from the same repository/Dockerfile, using the same application/database/mail variables. Give it the custom start command:

```sh
php artisan schedule:work
```

Deploy it only after the web service has completed migrations. Leave its public domain, healthcheck and Railway Cron Schedule unset; this is a continuously running scheduler. Use one scheduler replica. It runs the existing quote/announcement schedules; it does not import lottery history. The web service already includes a queue worker, so a separate worker service is not required for this setup.

## 4. Verify and update

In a Railway application container shell, run:

```sh
php artisan database:check
php artisan migrate:status
```

Check login, customer/purchase pages, existing uploaded images, the public batch link and `/up`. Update the latest lottery result when needed with:

```sh
php artisan lottery:sync-results
```

Do not run `migrate:fresh`, seed production data, regenerate the application key, or repeat `--history`. Future code deployments rebuild the image and run pending migrations automatically. Back up the database and upload volume before deployments that change stored data. This setup has not yet been deployed or container-tested in this session; Docker's local daemon is unavailable.

References: [Railway Dockerfiles](https://docs.railway.com/builds/dockerfiles), [Laravel on Railway](https://docs.railway.com/guides/laravel), [PostgreSQL connections](https://docs.railway.com/databases/postgresql), [persistent volumes](https://docs.railway.com/volumes), [PostgreSQL pg_restore](https://www.postgresql.org/docs/current/app-pgrestore.html).
