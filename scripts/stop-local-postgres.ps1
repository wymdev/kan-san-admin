$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$postgresRoot = Join-Path $projectRoot 'storage/postgres-local'
$control = Join-Path $postgresRoot 'pgsql/bin/pg_ctl.exe'
$process = Start-Process -FilePath $control -WorkingDirectory $postgresRoot -ArgumentList 'stop -D data -m fast' -WindowStyle Hidden -Wait -PassThru
if ($process.ExitCode -ne 0) { throw 'PostgreSQL did not stop. Inspect storage/postgres-local/server.log.' }
