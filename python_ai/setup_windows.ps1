$ErrorActionPreference = "Stop"
Set-Location $PSScriptRoot

function Test-RealPython([string] $Path) {
    if (-not $Path -or -not (Test-Path -LiteralPath $Path)) {
        return $false
    }
    if ($Path -like "*\Microsoft\WindowsApps\*") {
        return $false
    }
    try {
        & $Path --version *> $null
        return $LASTEXITCODE -eq 0
    } catch {
        return $false
    }
}

$candidates = [System.Collections.Generic.List[string]]::new()

$pythonCommand = Get-Command python.exe -ErrorAction SilentlyContinue
if ($pythonCommand) {
    $candidates.Add($pythonCommand.Source)
}

$python3Command = Get-Command python3.exe -ErrorAction SilentlyContinue
if ($python3Command) {
    $candidates.Add($python3Command.Source)
}

$searchPatterns = @(
    "$env:LOCALAPPDATA\Programs\Python\Python*\python.exe",
    "$env:ProgramFiles\Python*\python.exe",
    "${env:ProgramFiles(x86)}\Python*\python.exe",
    "C:\Python*\python.exe"
)

foreach ($pattern in $searchPatterns) {
    Get-Item -Path $pattern -ErrorAction SilentlyContinue |
        ForEach-Object { $candidates.Add($_.FullName) }
}

$pythonExe = $candidates |
    Select-Object -Unique |
    Where-Object { Test-RealPython $_ } |
    Select-Object -First 1

if (-not $pythonExe) {
    Write-Host ""
    Write-Host "Python could not be found." -ForegroundColor Red
    Write-Host "Install the 64-bit Windows installer from python.org."
    Write-Host "During installation, select: Add python.exe to PATH."
    Write-Host "Then close this window and run setup_windows.bat again."
    exit 1
}

Write-Host "Using Python: $pythonExe" -ForegroundColor Green
& $pythonExe -m venv (Join-Path $PSScriptRoot ".venv")
if ($LASTEXITCODE -ne 0) {
    throw "Python could not create the virtual environment."
}

$venvPython = Join-Path $PSScriptRoot ".venv\Scripts\python.exe"
& $venvPython -m pip install --upgrade pip
if ($LASTEXITCODE -ne 0) {
    throw "pip could not be upgraded."
}

& $venvPython -m pip install -r (Join-Path $PSScriptRoot "requirements.txt")
if ($LASTEXITCODE -ne 0) {
    throw "The required Python packages could not be installed."
}

if (-not (Test-Path -LiteralPath (Join-Path $PSScriptRoot ".env"))) {
    Copy-Item -LiteralPath (Join-Path $PSScriptRoot ".env.example") `
        -Destination (Join-Path $PSScriptRoot ".env")
}

Write-Host ""
Write-Host "Python environment and packages are ready." -ForegroundColor Green
exit 0
