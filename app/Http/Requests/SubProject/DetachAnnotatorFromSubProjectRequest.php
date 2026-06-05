<?php

declare(strict_types=1);

namespace App\Http\Requests\SubProject;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DetachAnnotatorFromSubProjectRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('detachAnnotator', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'annotator_id' => [
                'required',
                'integer',
                Rule::exists('annotation_assignments', 'user_id')
                    ->where('sub_project_id', (int) $this->route('subprojectId')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'annotator_id' => __('sub-projects.labels.annotator_ids'),
        ];
    }

    protected function prepareForValidation(): void {
        $this->merge([
            'annotator_id' => (int) $this->route('annotatorId'),
        ]);
    }
}
