@echo off
echo ============================================================
echo   TirtaPay - Sistem Informasi & Pembayaran Air Bersih
echo   Menjalankan Backend (Laravel 12) & Frontend (Vue 3)
echo ============================================================

REM Buka terminal untuk Laravel Backend
start "TirtaPay Backend (Laravel)" cmd /k "cd /d %~dp0backend && C:\laragon\bin\php\php-8.3.35-Win32-vs16-x64\php.exe artisan serve --port=8000"

REM Buka terminal untuk Vue 3 Frontend
start "TirtaPay Frontend (Vue 3)" cmd /k "cd /d %~dp0frontend && npm run dev"

echo.
echo Layanan sedang berjalan:
echo - Backend API : http://127.0.0.1:8000
echo - Frontend Web: http://localhost:5173
echo.
echo Silakan buka browser ke: http://localhost:5173
pause
