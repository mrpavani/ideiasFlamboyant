@echo off
REM ============================================================
REM  Idéias Flamboyant — inicia o servidor de desenvolvimento
REM  Acesse depois:  http://localhost:8000
REM ============================================================
cd /d "%~dp0"

if not exist ".env" (
  echo [!] Arquivo .env nao encontrado. Copiando de .env.example ...
  copy ".env.example" ".env" >nul
  echo [!] Edite o .env com os dados do seu MySQL antes de continuar.
  pause
)

echo Rodando diagnostico...
php bin\check.php
echo.
echo ------------------------------------------------------------
echo  Servidor em http://localhost:8000   (CTRL+C para parar)
echo ------------------------------------------------------------
php -S localhost:8000 -t public
