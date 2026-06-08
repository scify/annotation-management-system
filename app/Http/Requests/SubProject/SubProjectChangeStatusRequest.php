<?php

declare(strict_types=1);

namespace App\Http\Requests\SubProject;

use App\Enums\ProjectStatusEnum;
use App\Models\SubProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class SubProjectChangeStatusRequest extends FormRequest {
    public function authorize(): bool {
        $subProject = SubProject::query()->with('project')->findOrFail($this->integer('sub_project_id'));

        return Gate::allows('updateStatus', $subProject->project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'sub_project_id' => ['required', 'integer', 'exists:sub_projects,id'],
            'status' => ['required', Rule::enum(ProjectStatusEnum::class)],
        ];
    }
}
