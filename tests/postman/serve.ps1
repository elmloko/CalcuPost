$ErrorActionPreference = 'Stop'
$projectRoot = (Resolve-Path (Join-Path $PSScriptRoot '../..')).Path
if (Test-Path (Join-Path $projectRoot 'bootstrap/cache/config.php')) {
    throw 'Configuration is cached. Run artisan config:clear before starting the isolated test server.'
}
# These environment variables are scoped to this script/process, never written to .env.
$previousDatabase = $env:DB_DATABASE
$previousEnvironment = $env:APP_ENV
$previousPulse = $env:PULSE_ENABLED
try {
    $env:DB_DATABASE = 'calcupost_test'
    $env:APP_ENV = 'testing'
    $env:PULSE_ENABLED = 'false'
    Push-Location $projectRoot
    & './.tools/php84/php.exe' artisan serve --host=127.0.0.1 --port=8001 --no-reload
} finally {
    Pop-Location
    $env:DB_DATABASE = $previousDatabase
    $env:APP_ENV = $previousEnvironment
    $env:PULSE_ENABLED = $previousPulse
}
