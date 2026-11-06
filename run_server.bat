@echo off
cd /d "%~dp0"
echo ========================================================
echo   Campus Canteen Pre-Order and Queue Management System
echo ========================================================
echo.
echo Starting local PHP server at: http://localhost:8000
echo.
echo Press Ctrl+C in this window anytime to stop the server.
echo.
php -c php.ini -S localhost:8000
pause
