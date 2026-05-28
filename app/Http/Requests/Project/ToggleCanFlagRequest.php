<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ToggleCanFlagRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('toggleCanFlag', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'annotator_id' => [
                'required',
                'integer',
                Rule::exists('annotator_of_project', 'user_id')
                    ->where('project_id', $this->integer('project_id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'project_id' => __('projects.labels.project_id'),
            'annotator_id' => __('projects.labels.annotator_id'),
        ];
    }
}
