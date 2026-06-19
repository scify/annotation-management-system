<?php

declare(strict_types=1);

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class SendAnnouncementRequest extends FormRequest {
    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'sub_project_id' => ['nullable', 'integer', 'exists:sub_projects,id'],
            'body' => ['required', 'string', 'max:1000'],
        ];
    }
}
