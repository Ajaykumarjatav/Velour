@echo off
:: ══════════════════════════════════════════════════════════════════════
::  Velour — Queue Worker  (Windows / XAMPP)
::  Run this in a separate Command Prompt window while developing.
::  Keep it running — it processes email sending, reminders, etc.
:: ══════════════════════════════════════════════════════════════════════

:: Change this path to where you placed the project
set PROJECT_PATH=C:\xampp\htdocs\velour

:: Change this to your XAMPP PHP path if different
set PHP_PATH=C:\xampp\php\php.exe

echo =============================================
echo  Velour Queue Worker — starting...
echo  Project: %PROJECT_PATH%
echo  Press Ctrl+C to stop
echo =============================================

cd /d %PROJECT_PATH%
:loop
"%PHP_PATH%" artisan queue:work --sleep=3 --tries=3 --timeout=60 --max-time=3600
echo Worker stopped. Restarting in 5 seconds...
timeout /t 5
goto loop
