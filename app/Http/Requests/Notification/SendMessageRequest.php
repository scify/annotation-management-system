<?php

declare(strict_types=1);

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SendMessageRequest extends FormRequest {
    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'recipient_user_id' => ['required', 'integer', 'exists:users,id'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }
}
