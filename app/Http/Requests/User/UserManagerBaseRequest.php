<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class UserManagerBaseRequest extends FormRequest {
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

    /**
     * @return array<string, mixed>
     */
    protected function sharedRules(bool $collectionsRequired = true): array {
        $collectionRules = $collectionsRequired ? ['required', 'array', 'min:1'] : ['present', 'array'];

        return [
            'name' => ['required', 'string', 'max:255'],
            'project_ids' => $collectionRules,
            'project_ids.*' => ['integer', Rule::exists('projects', 'id')],
            'annotator_ids' => $collectionRules,
            'annotator_ids.*' => ['integer', Rule::exists('users', 'id')],
            'annotation_task_ids' => $collectionRules,
            'annotation_task_ids.*' => ['integer', Rule::exists('annotation_tasks', 'id')],
            'dataset_ids' => $collectionRules,
            'dataset_ids.*' => ['integer', Rule::exists('datasets', 'id')],
        ];
    }
}
