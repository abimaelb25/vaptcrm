@echo off
echo Iniciando CRM Grafica...
start "Laravel Server" cmd /k "cd /d %~dp0 && php artisan serve"
timeout /t 2 /nobreak >nul
start "Vite Assets" cmd /k "cd /d %~dp0 && npm run dev"
echo.
echo Servidores iniciados!
echo Laravel: http://127.0.0.1:8000
echo Vite:    http://localhost:5173
echo.
pause
