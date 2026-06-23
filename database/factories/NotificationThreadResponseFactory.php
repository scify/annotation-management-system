<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\NotificationThreadResponseEnum;
use App\Models\NotificationThread;
use App\Models\NotificationThreadResponse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NotificationThreadResponse>
 */
class NotificationThreadResponseFactory extends Factory {
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        return [
            'notification_thread_id' => NotificationThread::factory(),
            'response' => NotificationThreadResponseEnum::UNREPLIED,
            'sender_user_id' => User::factory(),
            'recipient_user_id' => User::factory(),
        ];
    }
}
