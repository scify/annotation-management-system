#!/bin/bash

ddev php artisan db:seed --class="Database\Seeders\Dummy\LexicalSemanticChangeDetection2026DummySeeder"

echo "LSCD dummy data seeded."