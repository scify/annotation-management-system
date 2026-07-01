<?php

declare(strict_types=1);

namespace App\Http\Requests\Annotation;

use Illuminate\Foundation\Http\FormRequest;

class ExitAnnotationRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'annotation_session_id' => ['required', 'integer'],
        ];
    }

    public function annotationSessionId(): int {
        return $this->integer('annotation_session_id');
    }
}
