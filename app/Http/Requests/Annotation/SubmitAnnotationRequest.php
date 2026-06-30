<?php

declare(strict_types=1);

namespace App\Http\Requests\Annotation;

use App\Enums\ConfidenceEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitAnnotationRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'mode' => ['nullable', 'string'],
            'annotation_id' => ['required', 'integer'],
            'annotation_session_id' => ['required', 'integer'],
            'annotations' => ['required', 'array'],
            'pending' => ['required', 'boolean'],
            'confidence' => ['nullable', 'string', Rule::enum(ConfidenceEnum::class)],
        ];
    }
}
