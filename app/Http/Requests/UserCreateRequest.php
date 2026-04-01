<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserCreateRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            // 'type' => [Rule::enum(RolesEnum::class)],
            'type' => 'exists:roles,name',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'type' => 'user role',
        ];
    }
}
