# Uploads the frontend/ folder to Hostinger over FTP (encrypted with TLS).
# Your login is read from deploy.env (git-ignored), never from this script.
#
# One-time setup:  copy deploy.env.example deploy.env   then put your FTP password in deploy.env
# Every update:    powershell -ExecutionPolicy Bypass -File .\deploy-frontend.ps1

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

if (-not (Test-Path deploy.env)) {
    Write-Host "deploy.env not found. Copy deploy.env.example to deploy.env and fill in your FTP login." -ForegroundColor Red
    exit 1
}

# Read KEY=VALUE lines from deploy.env
$cfg = @{}
Get-Content deploy.env | Where-Object { $_ -match '^\s*[^#].*=' } | ForEach-Object {
    $k, $v = $_ -split '=', 2
    $cfg[$k.Trim()] = $v.Trim()
}
foreach ($k in 'FTP_HOST', 'FTP_USER', 'FTP_PASS') {
    if (-not $cfg[$k]) { Write-Host "$k is missing in deploy.env" -ForegroundColor Red; exit 1 }
}
$remoteDir = if ($cfg['FTP_DIR']) { $cfg['FTP_DIR'].Trim('/') } else { 'public_html' }

# Never upload local secrets or junk
$skip = @('.env')
$root = (Resolve-Path frontend).Path
$files = Get-ChildItem -Path $root -Recurse -File -Force | Where-Object { $skip -notcontains $_.Name }

# Hand the login to curl through a temporary config file, so the password never shows in the process list
$curlCfg = New-TemporaryFile
try {
    "user = `"$($cfg['FTP_USER']):$($cfg['FTP_PASS'])`"" | Set-Content -Path $curlCfg -Encoding ascii

    $i = 0
    foreach ($f in $files) {
        $i++
        $rel = $f.FullName.Substring($root.Length + 1).Replace('\', '/')
        $url = "ftp://$($cfg['FTP_HOST'])/$remoteDir/$rel"
        Write-Host ("[{0}/{1}] {2}" -f $i, $files.Count, $rel)
        & curl.exe --silent --show-error --fail --ssl-reqd --ftp-create-dirs --connect-timeout 20 `
            -K $curlCfg -T $f.FullName $url
        if ($LASTEXITCODE -ne 0) {
            Write-Host "Upload failed for $rel (curl exit $LASTEXITCODE)." -ForegroundColor Red
            if ($LASTEXITCODE -eq 28) { Write-Host "Timed out: Hostinger may be blocking this network. Try another network or wait." -ForegroundColor Yellow }
            exit 1
        }
    }
}
finally {
    Remove-Item $curlCfg -Force -ErrorAction SilentlyContinue
}

Write-Host "Done: $($files.Count) files uploaded to $remoteDir. Open https://au.novatixsolution.com" -ForegroundColor Green
