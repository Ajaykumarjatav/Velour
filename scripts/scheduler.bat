@echo off
:: ══════════════════════════════════════════════════════════════════════
::  Velour — Laravel Scheduler  (Windows / XAMPP)
::
::  On Windows, Laravel's scheduler must be triggered by Windows Task
::  Scheduler every minute. This script is what Task Scheduler runs.
::
::  SETUP INSTRUCTIONS:
::  1. Open "Task Scheduler" (search in Start Menu)
::  2. Create Basic Task → name it "Velour Scheduler"
::  3. Trigger: Daily → repeat every 1 minute for 1 day
::  4. Action: Start a program
::     Program:   C:\xampp\htdocs\velour\scripts\scheduler.bat
::  5. Finish — the scheduler will now run every minute.
:: ══════════════════════════════════════════════════════════════════════

set PROJECT_PATH=C:\xampp\htdocs\velour
set PHP_PATH=C:\xampp\php\php.exe

cd /d %PROJECT_PATH%
"%PHP_PATH%" artisan schedule:run >> storage\logs\scheduler.log 2>&1
