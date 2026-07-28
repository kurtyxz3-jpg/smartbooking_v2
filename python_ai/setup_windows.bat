@echo off
title SmartBooking Python AI Setup
cd /d "%~dp0"
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup_windows.ps1"
echo.
if errorlevel 1 (
  echo Setup did not complete. Read the message above.
) else (
  echo SmartBooking Python AI setup completed successfully.
)
pause
