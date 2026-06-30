<?php

declare(strict_types=1);

namespace App\Http\Requests\Annotation;

use Illuminate\Foundation\Http\FormRequest;

class FlagAnnotationRequest extends FormRequest {
    public function authorize(): bool {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array {
        return [
            'mode' => ['nullable', 'string'],
            'flag_message' => ['required', 'string'],
            'annotator_instance_index' => ['required', 'integer'],
            'annotation_session_id' => ['required', 'integer'],
        ];
    }
}
