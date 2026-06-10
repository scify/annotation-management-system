<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ProposeOwnershipTransferRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->route('id'));

        return Gate::allows('proposeOwnership', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ];
    }
}
