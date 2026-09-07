$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$postgresRoot = Join-Path $projectRoot 'storage/postgres-local'
$control = Join-Path $postgresRoot 'pgsql/bin/pg_ctl.exe'
if (-not (Test-Path -LiteralPath $control)) {
    throw 'Local PostgreSQL binaries are missing. Install PostgreSQL and configure PG_MIGRATION_* as described in docs/postgresql-migration.md.'
}
if (-not (Test-Path -LiteralPath (Join-Path $postgresRoot 'data/PG_VERSION'))) {
    throw 'Local database cluster has not been initialized.'
}
$process = Start-Process -FilePath $control -WorkingDirectory $postgresRoot -ArgumentList 'start -D data -l server.log -o "-p 55432 -h 127.0.0.1"' -WindowStyle Hidden -PassThru -RedirectStandardOutput (Join-Path $postgresRoot 'startup.log') -RedirectStandardError (Join-Path $postgresRoot 'startup-error.log')
$process.WaitForExit()
if ($process.ExitCode -ne 0) {
    throw 'PostgreSQL did not start. Inspect storage/postgres-local/startup-error.log and server.log.'
}
