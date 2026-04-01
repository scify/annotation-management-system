<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rules\Password;

class UserStoreRequest extends FormRequest {
    public function authorize(): bool {
        return Gate::allows('create', User::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'email', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'exists:roles,name'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'name' => __('users.labels.name'),
            'username' => __('users.labels.username'),
            'email' => __('users.labels.email'),
            'password' => __('users.labels.password'),
            'password_confirmation' => __('users.labels.password_confirmation'),
            'role' => __('users.labels.role'),
        ];
    }
}
