#!/bin/bash

ddev php artisan migrate:fresh --seed &&
ddev php artisan db:seed --class="Database\Seeders\Dummy\DummyDataSeeder"

echo "Dummy database loaded."