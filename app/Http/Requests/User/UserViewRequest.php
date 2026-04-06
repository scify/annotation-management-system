<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserViewRequest extends FormRequest {
    public function __construct(protected RolesEnum $userType) {
        parent::__construct();
    }

    public function authorize(): bool {
        return Gate::allows('view', [User::class, $this->userType->value]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'type' => ['required', Rule::enum(RolesEnum::class)],
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
