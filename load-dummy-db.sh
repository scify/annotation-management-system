#!/bin/bash

ddev php artisan migrate:fresh --seed &&
ddev php artisan db:seed --class=DummyDataSeeder

echo "Dummy database loaded."