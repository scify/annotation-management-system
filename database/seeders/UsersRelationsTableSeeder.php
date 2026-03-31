<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UserRelationsEnum;
use App\Models\User;
use App\Models\UserRelation;
use Illuminate\Database\Seeder;

class UsersRelationsTableSeeder extends Seeder {
    /**
     * Run the database seeds.
     */
    public function run(): void {

        $manager = User::query()->where(['username' => 'scifymanager'])->first();
        $annotator = User::query()->where(['username' => 'scifyannotator'])->first();

        UserRelation::query()->updateOrCreate([
            'user_id' => $manager->id,
            'related_user_id' => $annotator->id,
            'relation_type' => UserRelationsEnum::ANNOTATOR_OF_MANAGER,
        ]);
    }
}
