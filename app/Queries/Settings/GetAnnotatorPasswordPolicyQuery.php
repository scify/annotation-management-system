<?php

declare(strict_types=1);

namespace App\Queries\Settings;

use App\Models\AnnotatorPasswordPolicy;

final readonly class GetAnnotatorPasswordPolicyQuery {
    public function get(): AnnotatorPasswordPolicy {
        /** @var AnnotatorPasswordPolicy */
        return AnnotatorPasswordPolicy::query()->firstOrFail();
    }
}
