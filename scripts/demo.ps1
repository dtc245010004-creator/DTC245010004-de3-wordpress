$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
Push-Location $projectRoot
try {
    docker compose ps
    if ($LASTEXITCODE -ne 0) { throw 'Cannot read Docker Compose status.' }

    $checks = @(
        @{ Name = 'Website'; Uri = 'http://localhost:8080/' },
        @{ Name = 'phpMyAdmin'; Uri = 'http://localhost:8080/db-admin/' },
        @{ Name = 'Grafana'; Uri = 'http://localhost:3001/login' },
        @{ Name = 'Prometheus'; Uri = 'http://localhost:9090/-/healthy' }
    )
    foreach ($check in $checks) {
        $response = Invoke-WebRequest -Uri $check.Uri -UseBasicParsing
        Write-Host ("{0,-14} HTTP {1}" -f $check.Name, $response.StatusCode)
    }

    $targets = (Invoke-RestMethod -Uri 'http://localhost:9090/api/v1/targets').data.activeTargets
    Write-Host "`nPrometheus scrape targets"
    $targets | ForEach-Object {
        [PSCustomObject]@{
            Job = $_.labels.job
            Target = $_.scrapeUrl
            Health = $_.health
        }
    } | Format-Table -AutoSize

    if (($targets | Where-Object health -ne 'up').Count -gt 0) {
        Write-Warning 'One or more scrape targets are not up yet. Review docker compose logs and retry after startup.'
    }
    Write-Host "`nOpen Grafana Explore and run queries in docs/logql-queries.md to verify Loki ingestion."
} finally {
    Pop-Location
}
