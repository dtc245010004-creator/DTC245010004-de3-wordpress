param(
    [switch]$Start
)

$ErrorActionPreference = 'Stop'
$projectRoot = Split-Path -Parent $PSScriptRoot
$envPath = Join-Path $projectRoot '.env'

$demoMySqlUser = 'wpuser'
$demoMySqlPassword = 'wp123456'
$demoGrafanaUser = 'admin'
$demoGrafanaPassword = 'admin123'
$demoWordPressUser = 'teacher'
$demoWordPressPassword = 'teacher123'
$demoWordPressEmail = 'teacher@example.test'

function New-HexSecret {
    $bytes = [byte[]]::new(32)
    $rng = [System.Security.Cryptography.RandomNumberGenerator]::Create()
    try { $rng.GetBytes($bytes) } finally { $rng.Dispose() }
    return ([BitConverter]::ToString($bytes) -replace '-', '').ToLowerInvariant()
}

function Read-EnvValue {
    param(
        [System.Collections.Generic.List[string]]$Lines,
        [string]$Name
    )

    $prefix = "$Name="
    foreach ($line in $Lines) {
        if ($line.StartsWith($prefix, [System.StringComparison]::Ordinal)) {
            return $line.Substring($prefix.Length)
        }
    }
    return $null
}

function Set-EnvValue {
    param(
        [System.Collections.Generic.List[string]]$Lines,
        [string]$Name,
        [string]$Value
    )

    $prefix = "$Name="
    for ($i = 0; $i -lt $Lines.Count; $i++) {
        if ($Lines[$i].StartsWith($prefix, [System.StringComparison]::Ordinal)) {
            $Lines[$i] = "$prefix$Value"
            return
        }
    }
    $Lines.Add("$prefix$Value")
}

$envLines = [System.Collections.Generic.List[string]]::new()
if (Test-Path $envPath) {
    foreach ($line in Get-Content -LiteralPath $envPath) { $envLines.Add($line) }
} else {
    $envLines.Add('MYSQL_DATABASE=dean3_shop')
    $envLines.Add("MYSQL_USER=$demoMySqlUser")
    $envLines.Add("MYSQL_PASSWORD=$demoMySqlPassword")
    $envLines.Add("MYSQL_ROOT_PASSWORD=$(New-HexSecret)")
    $envLines.Add("MYSQL_EXPORTER_PASSWORD=$(New-HexSecret)")
    $envLines.Add("GRAFANA_ADMIN_USER=$demoGrafanaUser")
    $envLines.Add("GRAFANA_ADMIN_PASSWORD=$demoGrafanaPassword")
    $envLines.Add("WORDPRESS_ADMIN_USER=$demoWordPressUser")
    $envLines.Add("WORDPRESS_ADMIN_PASSWORD=$demoWordPressPassword")
    $envLines.Add("WORDPRESS_ADMIN_EMAIL=$demoWordPressEmail")
    [IO.File]::WriteAllLines($envPath, $envLines, [Text.UTF8Encoding]::new($false))
    Write-Host 'Created .env with fixed classroom logins and random internal-only secrets.' -ForegroundColor Green
} else {
    Write-Host 'Found .env; it will be synchronized to the classroom logins when -Start is used.' -ForegroundColor Yellow
}

Push-Location $projectRoot
try {
    docker compose config --quiet
    if ($LASTEXITCODE -ne 0) { throw 'docker compose config failed.' }
    Write-Host 'Compose configuration is valid.' -ForegroundColor Green

    if ($Start) {
        # Start with the current .env first so a persistent MySQL volume can be
        # updated using its current root password before the app password changes.
        docker compose up -d --wait
        if ($LASTEXITCODE -ne 0) { throw 'docker compose up failed. Check that Docker Desktop is running.' }

        $databaseName = Read-EnvValue $envLines 'MYSQL_DATABASE'
        if ([string]::IsNullOrWhiteSpace($databaseName)) { $databaseName = 'dean3_shop' }
        if ($databaseName -notmatch '^[A-Za-z0-9_]+$') {
            throw 'MYSQL_DATABASE in .env must contain only letters, numbers, or underscores.'
        }

        # Update the existing MySQL account in place. This preserves db_data.
        $sql = @"
CREATE USER IF NOT EXISTS '$demoMySqlUser'@'%' IDENTIFIED BY '$demoMySqlPassword';
ALTER USER '$demoMySqlUser'@'%' IDENTIFIED BY '$demoMySqlPassword';
GRANT ALL PRIVILEGES ON $databaseName.* TO '$demoMySqlUser'@'%';
FLUSH PRIVILEGES;
"@
        $tempSqlPath = Join-Path ([IO.Path]::GetTempPath()) "demo-db-credentials-$([guid]::NewGuid().ToString('N')).sql"
        try {
            [IO.File]::WriteAllText($tempSqlPath, $sql, [Text.UTF8Encoding]::new($false))
            docker compose cp $tempSqlPath 'db:/tmp/demo-db-credentials.sql'
            if ($LASTEXITCODE -ne 0) { throw 'Could not prepare the MySQL credential update.' }
            docker compose exec -T db sh -c 'mysql --protocol=socket -uroot -p"$MYSQL_ROOT_PASSWORD" < /tmp/demo-db-credentials.sql'
            if ($LASTEXITCODE -ne 0) { throw 'Could not update the MySQL login. The existing root password in .env may not match db_data.' }
        } finally {
            docker compose exec -T db rm -f /tmp/demo-db-credentials.sql *> $null
            if (Test-Path $tempSqlPath) { Remove-Item -LiteralPath $tempSqlPath -Force }
        }

        # Grafana stores its admin password in grafana_data, so reset it there
        # before restarting with the matching .env value.
        docker compose exec -T grafana grafana-cli admin reset-admin-password $demoGrafanaPassword
        if ($LASTEXITCODE -ne 0) {
            docker compose exec -T grafana grafana cli admin reset-admin-password $demoGrafanaPassword
        }
        if ($LASTEXITCODE -ne 0) { throw 'Could not update the Grafana admin password.' }

        Set-EnvValue $envLines 'MYSQL_USER' $demoMySqlUser
        Set-EnvValue $envLines 'MYSQL_PASSWORD' $demoMySqlPassword
        Set-EnvValue $envLines 'GRAFANA_ADMIN_USER' $demoGrafanaUser
        Set-EnvValue $envLines 'GRAFANA_ADMIN_PASSWORD' $demoGrafanaPassword
        Set-EnvValue $envLines 'WORDPRESS_ADMIN_USER' $demoWordPressUser
        Set-EnvValue $envLines 'WORDPRESS_ADMIN_PASSWORD' $demoWordPressPassword
        Set-EnvValue $envLines 'WORDPRESS_ADMIN_EMAIL' $demoWordPressEmail
        [IO.File]::WriteAllLines($envPath, $envLines, [Text.UTF8Encoding]::new($false))

        docker compose config --quiet
        if ($LASTEXITCODE -ne 0) { throw 'docker compose config failed after updating .env.' }
        docker compose up -d --wait
        if ($LASTEXITCODE -ne 0) { throw 'Could not restart services with the classroom logins.' }

        docker compose exec -T `
            -e "DEMO_WP_ADMIN_USER=$demoWordPressUser" `
            -e "DEMO_WP_ADMIN_PASSWORD=$demoWordPressPassword" `
            -e "DEMO_WP_ADMIN_EMAIL=$demoWordPressEmail" `
            wordpress php /usr/local/bin/provision-wordpress.php
        if ($LASTEXITCODE -ne 0) { throw 'Could not prepare the WordPress teacher account.' }

        Write-Host 'All services are ready.' -ForegroundColor Green
        Write-Host 'WordPress: teacher / teacher123'
        Write-Host 'phpMyAdmin: wpuser / wp123456'
        Write-Host 'Grafana: admin / admin123'
        Write-Host 'Open the site at http://localhost:8080'
    } else {
        Write-Host 'Run .\scripts\setup.ps1 -Start to start services and synchronize saved logins.' -ForegroundColor Cyan
    }
} finally {
    Pop-Location
}
