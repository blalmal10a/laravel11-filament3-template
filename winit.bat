@echo off
setlocal enabledelayedexpansion

for %%I in (.) do set str=%%~nxI
echo %str%

set "snake="

:convert
for /f "tokens=*" %%a in ('echo %str%') do (
    set "temp=%%a"
    set "temp=!temp: =_!"
    set "temp=!temp:-=_!"
    set "temp=!temp:.=_!"
    call :tolower temp
    set "snake=!temp!"
)



:: Create the .env file with the required content
(
    echo APP_NAME="%str%"
    echo APP_ENV=local
    echo APP_KEY=base64:bbPTI5Bkep98BXQ+RUVEDaids6mJZAlDAAc8lgaE17c=
    echo APP_DEBUG=true
    echo APP_TIMEZONE=UTC
    echo APP_URL=http://localhost

    echo APP_LOCALE=en
    echo APP_FALLBACK_LOCALE=en
    echo APP_FAKER_LOCALE=en_US

    echo APP_MAINTENANCE_DRIVER=file
    echo # APP_MAINTENANCE_STORE=database

    echo BCRYPT_ROUNDS=12

    echo LOG_CHANNEL=stack
    echo LOG_STACK=single
    echo LOG_DEPRECATIONS_CHANNEL=null
    echo LOG_LEVEL=debug

    echo DB_CONNECTION=mysql
    echo DB_HOST=127.0.0.1
    echo DB_PORT=3306
    echo DB_DATABASE="%snake%"
    echo DB_USERNAME=root
    echo DB_PASSWORD=

    echo SESSION_DRIVER=database
    echo SESSION_LIFETIME=120
    echo SESSION_ENCRYPT=false
    echo SESSION_PATH=/
    echo SESSION_DOMAIN=null

    echo BROADCAST_CONNECTION=log
    echo FILESYSTEM_DISK=local
    echo QUEUE_CONNECTION=database

    echo CACHE_STORE=database
    echo CACHE_PREFIX=

    echo MEMCACHED_HOST=127.0.0.1

    echo REDIS_CLIENT=phpredis
    echo REDIS_HOST=127.0.0.1
    echo REDIS_PASSWORD=null
    echo REDIS_PORT=6379

    echo MAIL_MAILER=log
    echo MAIL_HOST=127.0.0.1
    echo MAIL_PORT=2525
    echo MAIL_USERNAME=null
    echo MAIL_PASSWORD=null
    echo MAIL_ENCRYPTION=null
    echo MAIL_FROM_ADDRESS="hello@example.com"
    echo MAIL_FROM_NAME="%APP_NAME%"

    echo AWS_ACCESS_KEY_ID=
    echo AWS_SECRET_ACCESS_KEY=
    echo AWS_DEFAULT_REGION=us-east-1
    echo AWS_BUCKET=
    echo AWS_USE_PATH_STYLE_ENDPOINT=false

    echo VITE_APP_NAME="%APP_NAME%"

    echo # SERVER_HOST=0.0.0.0
    echo SERVER_PORT=
    echo IMAGE_HOST=https://i.imgur.com
) > .env

echo .env file created successfully.
xcopy .env .env.example /I /Y
composer install & php artisan migrate &php artisan storage:link & php artisan key:generate &php artisan db:seed &php artisan serve

goto :eof
:tolower
for %%a in ("A=a" "B=b" "C=c" "D=d" "E=e" "F=f" "G=g" "H=h" "I=i" "J=j" "K=k" "L=l" "M=m" "N=n" "O=o" "P=p" "Q=q" "R=r" "S=s" "T=t" "U=u" "V=v" "W=w" "X=x" "Y=y" "Z=z") do (
    set %1=!%1:%%~a!
)
goto :eof

:LAST_FOLDER
if "%1"=="" (
    @echo %LAST%
    goto :EOF
)

set LAST=%1
SHIFT

goto :LAST_FOLDER
