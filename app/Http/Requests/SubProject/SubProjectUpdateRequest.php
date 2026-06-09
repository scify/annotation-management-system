<?php

declare(strict_types=1);

namespace App\Http\Requests\SubProject;

use App\Enums\SubProjectPriorityEnum;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubProjectUpdateRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('viewAny', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'priority' => ['required', 'string', Rule::in(array_column(SubProjectPriorityEnum::cases(), 'value'))],
            'is_flexible' => ['required', 'boolean'],
            'requires_confirmation' => ['required_if:is_flexible,true', 'nullable', 'boolean'],
            'minimum_annotations' => ['nullable', 'integer', 'min:1'],
            'scheduled_at' => ['nullable', 'date'],
            'deadline_at' => ['nullable', 'date', 'after_or_equal:scheduled_at'],
            'from_instance' => ['required', 'integer', 'min:1'],
            'to_instance' => ['required', 'integer', 'min:1', 'gte:from_instance'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'name' => __('sub-projects.labels.name'),
            'priority' => __('sub-projects.labels.priority'),
            'is_flexible' => __('sub-projects.labels.is_flexible'),
            'requires_confirmation' => __('sub-projects.labels.requires_confirmation'),
            'minimum_annotations' => __('sub-projects.labels.minimum_annotations'),
            'scheduled_at' => __('sub-projects.labels.scheduled_at'),
            'deadline_at' => __('sub-projects.labels.deadline_at'),
            'from_instance' => __('sub-projects.labels.from_instance'),
            'to_instance' => __('sub-projects.labels.to_instance'),
        ];
    }
}
