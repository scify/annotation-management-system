<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class ConnectAnnotatorsToManagerRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('connectAnnotators', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'annotator_ids' => ['required', 'array', 'min:1'],
            'annotator_ids.*' => ['integer', 'exists:users,id'],
        ];
    }
}
