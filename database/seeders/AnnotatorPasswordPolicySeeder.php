<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\PasswordCompositionEnum;
use App\Models\AnnotatorPasswordPolicy;
use Illuminate\Database\Seeder;

class AnnotatorPasswordPolicySeeder extends Seeder {
    public function run(): void {
        AnnotatorPasswordPolicy::query()->firstOrCreate(
            [],
            [
                'min_length' => 8,
                'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
                'mixed_case_required' => false,
            ]
        );
    }
}
