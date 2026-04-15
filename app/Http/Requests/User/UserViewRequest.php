<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UserViewRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('viewAny', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'search' => ['nullable', 'string', 'max:255'],
        ];
    }
}
