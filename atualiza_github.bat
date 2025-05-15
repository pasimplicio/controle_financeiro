@echo off
cd /d %~dp0
echo ================================
echo Atualizando repositório Git...
echo ================================
git add .
set /p msg=Digite a mensagem do commit: 
git commit -m "%msg%"
git push
@pause