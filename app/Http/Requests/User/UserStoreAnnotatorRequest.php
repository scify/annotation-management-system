<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserStoreAnnotatorRequest extends UserAnnotatorBaseRequest {
    public function authorize(): bool {
        return Gate::allows('create', [User::class, RolesEnum::ANNOTATOR->value]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return array_merge($this->sharedRules(), [
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')],
            'password' => ['required', 'confirmed', Password::defaults()],
            'password_confirmation' => ['required', 'string'],
        ]);
    }
}
