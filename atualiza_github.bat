@echo off
cd /d %~dp0
echo ================================
echo FORÇANDO INCLUSÃO NO GIT
echo ================================

:: Remove qualquer regra que ignore a pasta no cache local do Git
git rm -r --cached backup >nul 2>&1

:: Garante que os arquivos da pasta serão adicionados mesmo se estiverem no .gitignore
git add -f backup/*.sql

:: Adiciona os demais arquivos normalmente
git add .

:: Solicita a mensagem de commit
set /p msg=Digite a mensagem do commit: 
git commit -m "%msg%"

:: Envia para o GitHub
git push

@pause
