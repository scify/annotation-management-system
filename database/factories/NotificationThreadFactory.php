<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationThreadTypeEnum;
use App\Models\NotificationThread;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationThread>
 */
class NotificationThreadFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'type' => fake()->randomElement(NotificationThreadTypeEnum::cases()),
            'is_accepted' => null,
            'is_rejected' => null,
        ];
    }
}
