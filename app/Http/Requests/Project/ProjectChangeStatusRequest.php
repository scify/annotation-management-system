<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProjectChangeStatusRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->integer('project_id'));

        return Gate::allows('updateStatus', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'status' => ['required', Rule::enum(ProjectStatusEnum::class)],
        ];
    }
}
