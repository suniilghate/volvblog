Steps to Clone and run the project
git clone https://github.com/suniilghate/volvblog.git <projectFolder>
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
