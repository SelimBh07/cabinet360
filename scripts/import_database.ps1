# import_database.ps1
# Usage: Run this while you have `pscale connect` running in another terminal.
# From repository root:
# powershell.exe -ExecutionPolicy Bypass -File .\scripts\import_database.ps1

$localProxyHost = '127.0.0.1'
$localProxyPort = 3306
$mysqlClient = 'mysql'
$schemaFile = Join-Path $PSScriptRoot '..\\database.sql'

if (-Not (Test-Path $schemaFile)) {
    Write-Error "Schema file not found at $schemaFile"
    exit 1
}

Write-Host "Importing $schemaFile to ${localProxyHost}:${localProxyPort}"

# Read the full SQL and pipe it into the mysql client to avoid shell redirection issues
try {
    Get-Content -Raw -Path $schemaFile | & $mysqlClient -h $localProxyHost -P $localProxyPort -u root
    if ($LASTEXITCODE -ne 0) {
        Write-Error "Import failed with exit code $LASTEXITCODE"
        exit $LASTEXITCODE
    }
} catch {
    Write-Error "Import failed: $_"
    exit 1
}

Write-Host "Import complete."