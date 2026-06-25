<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AgreementEnum;

final class LexicalSemanticChangeDetectionAnnotationTaskService extends AnnotationTaskService {
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

        $answers = array_map(
            fn (array $item): mixed => array_values($item['annotations'])[0] ?? null,
            $valid,
        );

        $answers = array_filter($answers, fn (mixed $v): bool => $v !== null);

        if (count($answers) <= 1) {
            return AgreementEnum::UNDEFINED;
        }

        $counts = array_count_values(
            array_map(fn (mixed $v): string => is_scalar($v) ? (string) $v : '', $answers),
        );
        $maxCount = max($counts);
        $ratio = $maxCount / count($answers);

        return match (true) {
            $ratio >= 1.0 => AgreementEnum::HIGH,
            $ratio >= 0.6 => AgreementEnum::MEDIUM,
            default => AgreementEnum::LOW,
        };
    }
}
