title DockerStarter
@echo off
echo Nastavuji soubor hosts
for /F "delims=" %%a in ('findstr /c:VIRTUAL_HOST docker-compose.yml') do set var=%%a
for /F "delims=" %%a in ('findstr /c:%var:~20% c:\windows\system32\drivers\etc\hosts') do set var2=%%a
IF "%var2%" == "" (
	echo %n%127.0.0.1 %var:~20% >> c:\windows\system32\drivers\etc\hosts
	)
echo Cistim cache
docker container prune -f
echo Startuji Docker...
docker-compose up --build
pause