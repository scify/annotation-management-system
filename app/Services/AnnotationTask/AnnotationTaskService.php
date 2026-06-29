<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AgreementEnum;
use App\Enums\ConfidenceEnum;
use App\Queries\Dataset\GetDatasetInstanceQuery;

abstract class AnnotationTaskService {
    public function __construct(
        private readonly GetDatasetInstanceQuery $datasetInstanceQuery,
    ) {}

    /** @param array<string, mixed> $annotations */
    abstract public function save(int $annotationId, array $annotations, bool $pending, ?ConfidenceEnum $confidence): void;

    /** @return array<string, mixed> */
    abstract public function getTaskRelatedData(int $datasetInstanceId, int $subProjectId): array;

    /**
     * @param  array<int, array{annotations: array<string, mixed>|null, pending: bool}>  $annotationsValues
     */
    abstract public function computeAgreement(array $annotationsValues): AgreementEnum;

    /** @return array<string, mixed> */
    protected function getContent(int $datasetInstanceId): array {
        return $this->datasetInstanceQuery->getById($datasetInstanceId)->content;
    }
}
