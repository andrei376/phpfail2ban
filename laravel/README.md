
## Install

git clone https://github.com/andrei376/phpfail2ban.git


configure your web server document root to ./laravel/public/

copy .env.example to .env

edit .env and configure mysql access, mail sending settings 

run in ./laravel directory:

php artisan key:generate --ansi

php artisan migrate

php artisan storage:link 


create user using console:
php artisan user:create email password



add system cron (every minute):

`* * * * * WEBSERVER_USER cd /srv/www/phpfail2ban/laravel/ && /usr/bin/php artisan schedule:run >> /dev/null 2>&1`

change directory to phpfail2ban location

WEBSERVER_USER = run the cron as the webserver user (ex: www-data)
