#!/bin/bash

ddev php artisan db:seed --class="Database\Seeders\AnnotationTasks\LexicalSemanticChangeDetection2026Seeder"

echo "Annotation tasks seeded."
