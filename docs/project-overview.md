# Project overview

Kan-San is a Laravel 12 application with server-rendered Blade pages. Vite builds Tailwind 4, the Tailwick template, Preline, Lucide, ApexCharts, Flatpickr and other widgets. It is not a React SPA. `resources/views/app-versions/mobileapp.tsx` is reference code, not the admin entry point.

## Request and data flow

- `routes/web.php`: public lottery checking and customer batch links, login/OTP, and protected administration. Controllers enforce Spatie permissions.
- `routes/api.php`: mobile customer authentication and purchases, app content/configuration, results and push notifications; Sanctum provides tokens.
- `app/Models`: Eloquent relationships connect customers, lottery tickets, purchases, draw results, administrators, roles and permissions.
- `app/Services/LotteryResultCheckerService.php`: draw matching, primary and secondary result checking, historical results and prize display.
- `app/Services/SecondarySalesService.php`: secondary transaction numbering. Secondary sales preserve individual public tokens and customer/draw batch tokens.
- OCR, notification and OTP services integrate external systems. Scheduled commands send quotes and announcements. Preserve their configuration and existing tokens during migration.

## Presentation

`layouts.vertical` owns the admin shell and global flash/validation messages. `resources/css/themes.css` contains light/dark color tokens; `resources/css/custom/_admin.css` owns shared admin controls and surfaces. Anonymous Blade components are in `resources/views/components/ui`.

List/filter/table/control markup is shared across the admin views, including the bundled example pages that use the admin shell. Public lottery pages, email templates, printable exports and authentication layouts are separate surfaces. Existing complex widgets retain their IDs, data attributes and page-specific interaction code.

See [the UI guide](ui-components.md) for component contracts and usage.

## Database decision

PostgreSQL fits this application's relational sales, permissions, foreign keys and aggregate reporting. Laravel supports PostgreSQL directly. MongoDB would require broader changes to the data model and relational queries. [Laravel database documentation](https://laravel.com/docs/12.x/database)

The local installation now runs on PostgreSQL. The original MySQL database and private pre-cutover environment remain available for reconciliation or rollback; source access requires those original settings because the active `DB_*` values now refer to PostgreSQL. PostgreSQL-specific differences are isolated in `app/Support/DatabaseSql.php` and driver-aware schema migrations. See [the migration guide](postgresql-migration.md).

## Existing limitations found during review

- `prize_won` contains display labels, sometimes including formatted reward text. Existing financial totals implicitly converted that text to numbers in MySQL. The compatibility helper preserves that legacy conversion; it does not reconstruct monetary rewards. A separately scoped numeric reward field and historical reconciliation are needed for dependable payout reporting.
- Some legacy and template pages retain specialized inline styles. Shared admin primitives now use the central design rules; public and auth surfaces have independent layouts.
- The Vite build emits upstream Bootstrap/Sass deprecation warnings and missing image warnings for template examples.
