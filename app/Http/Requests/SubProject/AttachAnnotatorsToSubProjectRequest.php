<?php

declare(strict_types=1);

namespace App\Http\Requests\SubProject;

use App\Models\SubProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class AttachAnnotatorsToSubProjectRequest extends FormRequest {
    public function authorize(): bool {
        $subProject = SubProject::query()->findOrFail($this->route('subprojectId'));

        return Gate::allows('attachSubProjectAnnotators', $subProject);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'annotator_ids' => ['required', 'array', 'min:1'],
            'annotator_ids.*' => [
                'integer',
                Rule::exists('annotator_of_project', 'user_id')
                    ->where('project_id', (int) $this->route('projectId')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'annotator_ids' => __('sub-projects.labels.annotator_ids'),
        ];
    }
}
