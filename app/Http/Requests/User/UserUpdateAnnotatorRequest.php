<?php

declare(strict_types=1);

namespace App\Http\Requests\User;

use App\Models\User;
use App\Services\Settings\AnnotatorPasswordPolicyService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class UserUpdateAnnotatorRequest extends UserAnnotatorBaseRequest {
    public function __construct(
        private readonly AnnotatorPasswordPolicyService $policyService,
    ) {}

    public function authorize(): bool {
        return Gate::allows('update', $this->route('user'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        $routeUser = $this->route('user');
        $userId = $routeUser instanceof User ? $routeUser->id : 0;

        return array_merge($this->sharedRules(), [
            'username' => ['required', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'password' => ['sometimes', 'confirmed', $this->policyService->buildRule()],
            'password_confirmation' => ['sometimes', 'string'],
        ]);
    }

    protected function passedValidation(): void {
        if (empty($this->input('password'))) {
            $this->request->remove('password');
        }
    }
}
