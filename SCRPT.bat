@echo off
cd /d "%~dp0"

where php >nul 2>&1
if errorlevel 1 (
    echo PHP nao foi encontrado. Instale o PHP ou adicione-o ao PATH do Windows.
    pause
    exit /b 1
)


start "" http://127.0.0.1:8000/index.html
php -S 127.0.0.1:8000