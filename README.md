Hello,
Installation steps:

1- Navigate to the root directory
2- Run cmd : "composer install"
3- Run cmd : "php artisan key:generate"
3- Fill out DB_DATABASE & DB_USERNAME & DB_PASSWORD in .env file
4- Run cmd : "php artisan setup:process" to create the migration and fill the data into tables
5- Unit testings are attached run cmd : "./vendor/bin/phpunit" then "php artisan test"
6- Postman collection is attached within the root directory called MarineTraffic Task.postman_collection.json

Regards.
