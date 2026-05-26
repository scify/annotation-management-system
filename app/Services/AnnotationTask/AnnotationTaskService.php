<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AgreementEnum;

abstract class AnnotationTaskService {
    /**
     * @param  array<int, array{annotations: array<string, mixed>|null, pending: bool}>  $annotationsValues
     */
    abstract public function computeAgreement(array $annotationsValues): AgreementEnum;
}
