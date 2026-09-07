# Thai government results API

Results now come from [ผลหวย.com](https://xn--t3cmiit.com). No provider API key is required. `LOTTERY_RESULTS_BASE_URL` defaults to `https://xn--t3cmiit.com`.

The local PostgreSQL installation completed its one-time history import on 7 September 2026: 20 draws synced, 40 total draws, and all 25 preexisting IDs retained. Normal updates now use the latest-only command or button.

- Latest sync: one `GET /api/public/results?lottery=thai-government&limit=1` request.
- History import: one `GET /api/public/results?lottery=thai-government&limit=20` request. The import is not scheduled and does not paginate or request individual dates.
- Successful history completion is stored in `external_syncs.history_imported_at`. Later attempts return without contacting the API. The marker survives cache clears and database migration. Failed requests or invalid results leave it incomplete so an explicit retry remains possible.

```sh
php artisan migrate
php artisan lottery:sync-results
php artisan lottery:sync-results --history
```

The admin results page has POST actions for **Sync latest** and **Import history once**, with CSRF protection and the `lottery-edit` permission. After the history import, its action is replaced by a completed badge.

The mapper checks the lottery, date, all nine prize categories, number lengths and prize counts. Numbers remain strings so leading zeros survive. It rejects incomplete or placeholder results before saving anything. Updates preserve existing result IDs and purchase relationships. Existing older draws are retained. A database transaction covers the full import and its completion marker; latest/history operations share a row lock to prevent concurrent duplicate imports.

The provider's prize slugs map to the IDs already used by ticket checking. Source links appear on admin and public result pages. RapidAPI and GLO fallback fetching, per-date requests and generated history dates have been removed.

Environment cleanup removes unused Redis, Memcached, AWS, legacy mail DSN and template-only variables for the current local-filesystem/database-cache/Brevo setup. `API_KEY` remains an independent credential for this app's mobile endpoints; it is unrelated to the public results provider. OCR and mail credentials remain supported.

Check connections without changing data:

```sh
php artisan database:check
php artisan database:check --connection=pgsql_migration
```

Keep the existing database connection until the PostgreSQL target authenticates and the copy has been verified. Do not import history independently into both databases; copy its persisted marker with the data.
