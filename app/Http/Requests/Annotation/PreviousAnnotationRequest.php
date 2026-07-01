<?php

declare(strict_types=1);

namespace App\Http\Requests\Annotation;

use App\Enums\AnnotationInstanceFilterEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PreviousAnnotationRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'active_filter' => ['nullable', 'string', Rule::enum(AnnotationInstanceFilterEnum::class)],
        ];
    }

    public function activeFilter(): AnnotationInstanceFilterEnum {
        $value = $this->string('active_filter')->toString();

        return $value !== '' ? AnnotationInstanceFilterEnum::from($value) : AnnotationInstanceFilterEnum::All;
    }
}
