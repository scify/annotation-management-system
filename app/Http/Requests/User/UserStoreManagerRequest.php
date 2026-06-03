<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreManagerRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('create', [User::class, RolesEnum::ANNOTATION_MANAGER->value]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'email' => ['required', 'email', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'project_ids' => ['required', 'array', 'min:1'],
            'project_ids.*' => ['integer', Rule::exists('projects', 'id')],
            'annotator_ids' => ['required', 'array', 'min:1'],
            'annotator_ids.*' => ['integer', Rule::exists('users', 'id')],
            'annotation_task_ids' => ['required', 'array', 'min:1'],
            'annotation_task_ids.*' => ['integer', Rule::exists('annotation_tasks', 'id')],
            'dataset_ids' => ['required', 'array', 'min:1'],
            'dataset_ids.*' => ['integer', Rule::exists('datasets', 'id')],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'name' => __('users.labels.name'),
            'username' => __('users.labels.username'),
            'email' => __('users.labels.email'),
            'password' => __('users.labels.password'),
            'password_confirmation' => __('users.labels.password_confirmation'),
            'project_ids' => __('users.labels.project_ids'),
            'annotator_ids' => __('users.labels.annotator_ids'),
            'annotation_task_ids' => __('users.labels.annotation_task_ids'),
            'dataset_ids' => __('users.labels.dataset_ids'),
        ];
    }
}
