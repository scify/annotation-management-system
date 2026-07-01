<?php

declare(strict_types=1);

namespace App\Services\AnnotationTask;

use App\Enums\AgreementEnum;
use App\Enums\ConfidenceEnum;
use App\Queries\Annotation\UpdateAnnotationQuery;
use App\Queries\Dataset\GetDatasetInstanceQuery;
use App\Queries\Project\GetProjectByIdQuery;

final class LexicalSemanticChangeDetectionAnnotationTaskService extends AnnotationTaskService {
    public function __construct(
        GetDatasetInstanceQuery $datasetInstanceQuery,
        private readonly GetProjectByIdQuery $projectByIdQuery,
        private readonly UpdateAnnotationQuery $updateAnnotationQuery,
    ) {
        parent::__construct($datasetInstanceQuery);
    }

    /** @param array<string, mixed> $annotations */
    public function save(int $annotationId, array $annotations, bool $pending, ?ConfidenceEnum $confidence): void {
        $this->updateAnnotationQuery->update($annotationId, $annotations, $pending, $confidence);
    }

    /** @return array{yes: array{is_selected: bool}, no: array{is_selected: bool}, cannot_decide?: array{is_selected: bool}} */
    public function getAnnotationSchema(int $projectId): array {
        $config = $this->projectByIdQuery->get($projectId)->annotation_task_configuration ?? [];

        $configById = [];
        foreach ($config as $item) {
            $configById[$item['id']] = $item['answer'];
        }

        $allowCannotDecide = ($configById[1] ?? 'No') === 'Yes';

        $schema = [
            'yes' => ['is_selected' => false],
            'no' => ['is_selected' => false],
        ];

        if ($allowCannotDecide) {
            $schema['cannot_decide'] = ['is_selected' => false];
        }

        return $schema;
    }

    /** @return array<string, mixed> */
    public function getTaskRelatedData(int $datasetInstanceId, int $projectId): array {
        $content = $this->getContent($datasetInstanceId);

        $project = $this->projectByIdQuery->get($projectId);
        $config = $project->annotation_task_configuration ?? [];

        $configById = [];
        foreach ($config as $item) {
            $configById[$item['id']] = $item['answer'];
        }

        return [
            'word' => $content['word'],
            'senses' => $content['senses'],
            'first_corpus_sentence' => $content['first_corpus_sentence'],
            'second_corpus_sentence' => $content['second_corpus_sentence'],
            'allow_confidence' => ($configById[0] ?? 'No') === 'Yes',
        ];
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
