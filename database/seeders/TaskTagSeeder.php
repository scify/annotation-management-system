<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\TaskTag;
use Illuminate\Database\Seeder;

class TaskTagSeeder extends Seeder {
    public function run(): void {
        $tags = [
            'text', 'image', 'audio', 'video',
            'classification', 'transcription', 'translation', 'sentiment analysis', 'summarisation',
        ];

        foreach ($tags as $tag) {
            TaskTag::query()->firstOrCreate(['name' => $tag]);
        }
    }
}
