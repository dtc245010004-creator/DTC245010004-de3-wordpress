param(
    [switch]$Start
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env'

function New-HexSecret {
    $bytes = [byte[]]::new(32)
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($bytes) } finally { $rng.Dispose() }
    return ([BitConverter]::ToString($bytes) -replace '-', '').ToLowerInvariant()
}

if (-not (Test-Path $envPath)) {
    $lines = @(
        'MYSQL_DATABASE=dean3_shop',
        'MYSQL_USER=wpuser',
        "MYSQL_PASSWORD=$(New-HexSecret)",
        "MYSQL_ROOT_PASSWORD=$(New-HexSecret)",
        "MYSQL_EXPORTER_PASSWORD=$(New-HexSecret)",
        'GRAFANA_ADMIN_USER=admin',
        "GRAFANA_ADMIN_PASSWORD=$(New-HexSecret)"
    )
    [IO.File]::WriteAllLines($envPath, $lines, [Text.UTF8Encoding]::new($false))
    Write-Host 'Created .env with unique 256-bit secrets.' -ForegroundColor Green
} else {
    Write-Host '.env already exists; keeping the current credentials.' -ForegroundColor Yellow
}

Push-Location $projectRoot
try {
    docker compose config --quiet
    if ($LASTEXITCODE -ne 0) { throw 'docker compose config failed.' }
    Write-Host 'Compose configuration is valid.' -ForegroundColor Green
    if ($Start) {
        docker compose up -d --wait
        if ($LASTEXITCODE -ne 0) { throw 'docker compose up failed.' }
        Write-Host 'Services are starting. Open http://localhost:8080' -ForegroundColor Green
    }
} finally {
    Pop-Location
}
