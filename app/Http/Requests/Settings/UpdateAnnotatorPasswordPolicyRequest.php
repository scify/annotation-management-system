<?php

declare(strict_types=1);

namespace App\Http\Requests\Settings;

use App\Enums\PasswordCompositionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAnnotatorPasswordPolicyRequest extends FormRequest {
    public function authorize(): bool {
        return $this->user()?->hasRole('admin') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array {
        return [
            'min_length' => ['required', 'integer', 'min:4', 'max:128'],
            'composition_mode' => ['required', 'string', Rule::enum(PasswordCompositionEnum::class)],
            'mixed_case_required' => ['required', 'boolean'],
        ];
    }
}
