<?php

declare(strict_types=1);

namespace App\Http\Requests\Annotation;

use Illuminate\Foundation\Http\FormRequest;

class SendToManagerAnnotationRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'message' => ['required', 'string'],
            'annotator_instance_index' => ['required', 'integer'],
            'annotation_session_id' => ['required', 'integer'],
        ];
    }
}
