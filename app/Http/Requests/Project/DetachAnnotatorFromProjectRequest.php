<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class DetachAnnotatorFromProjectRequest extends FormRequest {
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
                Rule::exists('annotator_of_project', 'user_id')
                    ->where('project_id', (int) $this->route('id')),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'annotator_id' => __('projects.labels.annotator_id'),
        ];
    }

    protected function prepareForValidation(): void {
        $this->merge([
            'annotator_id' => (int) $this->route('annotatorId'),
        ]);
    }
}
