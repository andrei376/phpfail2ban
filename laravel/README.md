
## Install

git clone https://github.com/andrei376/phpfail2ban.git

configure your web server document root to ./laravel/public/

copy .env.example to .env
edit .env and configure mysql access, mail sending settings 

php artisan migrate

php artisan storage:link 


enable register to create an user:
laravel/config/fortify.php:  enable "Features::registration(),"

disable it after user creation


