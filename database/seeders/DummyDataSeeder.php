<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {
        $this->call([
            DatabaseSeeder::class,
            DummyAnnotationTasksSeeder::class,
            DummyDatasetsSeeder::class,
            DummyUsersSeeder::class,
            DummyProjectSeeder::class,
        ]);
    }
}
