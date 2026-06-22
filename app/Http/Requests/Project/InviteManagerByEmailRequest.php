<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class InviteManagerByEmailRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->route('id'));

        return Gate::allows('inviteManager', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
        ];
    }
}
