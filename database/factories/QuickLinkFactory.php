<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\NotificationThread;
use App\Models\QuickLink;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<QuickLink>
 */
class QuickLinkFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'notification_thread_id' => NotificationThread::factory(),
            'label' => fake()->words(3, true),
            'url' => '/' . fake()->slug(),
        ];
    }
}
