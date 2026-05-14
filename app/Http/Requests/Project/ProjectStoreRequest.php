<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectStoreRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('create', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'annotation_task_id' => ['required', 'integer', Rule::exists('annotation_tasks', 'id')->whereNull('deleted_at')],
            'dataset_id' => ['required', 'integer', 'exists:datasets,id'],
            'is_instance_shuffled' => ['required', 'boolean'],
            'annotation_task_configuration' => ['nullable', 'array'],
            'annotation_task_configuration.*.id' => ['required', 'integer'],
            'annotation_task_configuration.*.answer' => ['required', 'string'],
            'restricted_visibility' => ['required', 'boolean'],
            'annotator_ids' => ['required', 'array', 'min:1'],
            'annotator_ids.*' => ['integer', 'exists:users,id'],
            'co_manager_ids' => ['nullable', 'array'],
            'co_manager_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'name' => __('projects.labels.name'),
            'annotation_task_id' => __('projects.labels.annotation_task_id'),
            'dataset_id' => __('projects.labels.dataset_id'),
            'is_instance_shuffled' => __('projects.labels.is_instance_shuffled'),
            'annotation_task_configuration' => __('projects.labels.annotation_task_configuration'),
            'restricted_visibility' => __('projects.labels.restricted_visibility'),
            'annotator_ids' => __('projects.labels.annotator_ids'),
            'co_manager_ids' => __('projects.labels.co_manager_ids'),
        ];
    }
}
