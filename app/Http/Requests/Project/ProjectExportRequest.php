<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectExportRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('export', Project::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'subproject_ids' => ['required', 'array', 'min:1'],
            'subproject_ids.*' => ['integer', Rule::exists('sub_projects', 'id')->where('project_id', (int) $this->route('id'))],
        ];
    }
}
