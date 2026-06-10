<?php

declare(strict_types=1);

namespace App\Http\Requests\Project;

use App\Models\Project;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class AcceptOwnershipTransferRequest extends FormRequest {
    public function authorize(): bool {
        $project = Project::query()->findOrFail($this->route('id'));

        return Gate::allows('acceptOwnership', $project);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [];
    }
}
