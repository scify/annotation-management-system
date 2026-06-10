<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class RemoveManagerFromProjectRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->route('id'));

        return Gate::allows('removeManager', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'manager_id' => [
                'required',
                'integer',
                Rule::exists('project_managers', 'user_id')
                    ->where('project_id', (int) $this->route('id')),
            ],
        ];
    }

    protected function prepareForValidation(): void {
        $this->merge([
            'manager_id' => (int) $this->route('managerId'),
        ]);
    }
}
