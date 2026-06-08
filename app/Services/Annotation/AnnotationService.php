<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Enums\AgreementEnum;
use App\Enums\AnnotationTaskTypeEnum;
use App\Enums\ConfidenceEnum;
use App\Queries\SubProject\GetAnnotationsBySubProjectQuery;
use App\Services\AnnotationTask\AnnotationTaskServiceFactory;

readonly class AnnotationService {
    public function __construct(
        private GetAnnotationsBySubProjectQuery $annotationsBySubProjectQuery,
        private AnnotationTaskServiceFactory $taskServiceFactory,
    ) {}

    /**
     * Returns per-dataset-instance annotation data for a sub-project.
     *
     * @return array<int, array{
     *     dataset_instance_id: int,
     *     annotated: int,
     *     planned_annotations: int,
     *     agreement: AgreementEnum,
     *     annotations: array<int, array{
     *         id: int,
     *         annotator_data: array{user_id: int, username: string, role: string|null},
     *         last_edited_by_data: array{user_id: int, username: string|null, role: string|null}|null,
     *         updated_at: string|null,
     *         confidence: ConfidenceEnum|null,
     *         status: string
     *     }>
     * }>
     */
    public function getAnnotationsData(int $subProjectId, AnnotationTaskTypeEnum $taskType): array {
        $taskService = $this->taskServiceFactory->make($taskType);
        $rows = $this->annotationsBySubProjectQuery->get($subProjectId);

        $result = [];

        foreach ($rows as $datasetInstanceId => $instanceData) {
            $result[] = [
                'dataset_instance_id' => $datasetInstanceId,
                'annotated' => $instanceData['annotated'],
                'planned_annotations' => $instanceData['planned_annotations'],
                'agreement' => $taskService->computeAgreement($instanceData['annotations_values']),
                'annotations' => $instanceData['annotations'],
            ];
        }

        return $result;
    }
}
