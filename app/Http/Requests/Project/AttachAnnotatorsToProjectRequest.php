<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AttachAnnotatorsToProjectRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->route('id'));

        return Gate::allows('attachAnnotators', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'annotator_ids' => ['required', 'array', 'min:1'],
            'annotator_ids.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'annotator_ids' => __('projects.labels.annotator_ids'),
        ];
    }
}
