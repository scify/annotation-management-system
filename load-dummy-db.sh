#!/bin/bash

rm -f database/schema/mysql-schema.sql &&
ddev php artisan migrate:fresh --seed &&
ddev php artisan schema:dump &&
ddev php artisan db:seed --class="Database\Seeders\Dummy\DummyDataSeeder"

echo "Dummy database loaded."