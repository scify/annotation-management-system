<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AgreementEnum;
use Random\RandomException;

final class DummyAnnotationTaskService extends AnnotationTaskService {
    /** @return array<string, mixed> */
    public function getTaskRelatedData(int $datasetInstanceId, int $subProjectId): array {
        return [];
    }

    /**
     * @param  array<int, array{annotations: array<string, mixed>|null, pending: bool}>  $annotationsValues
     */
    public function computeAgreement(array $annotationsValues): AgreementEnum {
        $valid = array_filter(
            $annotationsValues,
            fn (array $item): bool => ! $item['pending'] && $item['annotations'] !== null,
        );

        if (count($valid) <= 1) {
            return AgreementEnum::UNDEFINED;
        }

        try {
            return [AgreementEnum::HIGH, AgreementEnum::MEDIUM, AgreementEnum::LOW][random_int(0, 2)];
        } catch (RandomException) {
            return AgreementEnum::UNDEFINED;
        }
    }
}
