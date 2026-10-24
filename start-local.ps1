# Starts the whole project on this PC:
#   backend (FastAPI)  -> http://127.0.0.1:8000
#   PHP UI             -> http://127.0.0.1:8080   <- open this one
# Run from this folder:  powershell -ExecutionPolicy Bypass -File .\start-local.ps1

$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

# Python packages live in a short-path venv (Windows can't handle long paths in venvs)
$venv = "$env:USERPROFILE\dscomp-venv"
if (-not (Test-Path "$venv\Scripts\python.exe")) {
    python -m venv $venv
    & "$venv\Scripts\python.exe" -m pip install -r backend\requirements.txt
}

$php = (Get-Command php -ErrorAction SilentlyContinue).Source
if (-not $php) { $php = "C:\xampp\php\php.exe" }

if (-not (Select-String -Path backend\.env -Pattern '^E2B_API_KEY=e2b_' -Quiet)) {
    Write-Host "E2B_API_KEY is missing in backend\.env - add it first (https://e2b.dev > Dashboard > API Keys)." -ForegroundColor Red
    exit 1
}

Start-Process -FilePath "$venv\Scripts\python.exe" -ArgumentList "-m uvicorn backend.main:app --port 8000" -WorkingDirectory $PSScriptRoot
Start-Process -FilePath $php -ArgumentList "-S 127.0.0.1:8080 -t frontend" -WorkingDirectory $PSScriptRoot

Write-Host "Backend: http://127.0.0.1:8000/health"
Write-Host "Open the UI: http://127.0.0.1:8080" -ForegroundColor Green
