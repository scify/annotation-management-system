<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

abstract class UserAnnotatorBaseRequest extends FormRequest {
    /**
     * @return array<string, string>
     */
    public function attributes(): array {
        return [
            'name' => __('users.labels.name'),
            'username' => __('users.labels.username'),
            'password' => __('users.labels.password'),
            'password_confirmation' => __('users.labels.password_confirmation'),
            'manager_ids' => __('users.labels.manager_ids'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function sharedRules(): array {
        return [
            'name' => ['required', 'string', 'max:255'],
            'manager_ids' => ['required', 'array', 'min:1'],
            'manager_ids.*' => ['integer', Rule::exists('users', 'id')],
        ];
    }
}
