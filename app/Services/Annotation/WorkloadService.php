<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Services\User\UserService;

readonly class WorkloadService {
    public function __construct(private UserService $userService) {}

    /**
     * Returns min-max normalized workloads ([0.1, 0.9]) for each annotator ID — both
     * the total remaining workload and the remaining workload per subproject.
     *
     * Uses a hybrid scale: 0 (done) is the absolute floor → always 0.1; the most-loaded
     * annotator in the set is the relative ceiling → always 0.9. Annotators with identical
     * positive workload all receive 0.9. Falls back to 0.5 when range is zero and min > 0.
     *
     * @param  array<int, int>  $annotatorIds
     * @param  array<int, int>|null  $subProjectIds  When provided, restrict to these subprojects only
     *
     * @return array<int, array{total: float, per_subproject: array<int, float>}>
     */
    public function computeNormalizedWorkloads(array $annotatorIds, ?array $subProjectIds = null): array {
        if ($annotatorIds === []) {
            return [];
        }

        $workloads = $this->userService->getWorkloads($annotatorIds, $subProjectIds);

        // Absolute floor at 0 so done annotators always map to 0.1; the most-loaded
        // annotator in the set naturally becomes the ceiling and maps to 0.9.
        $totalRaws = array_map(fn (array $w): int => $w['total_workload'], $workloads);
        $totalRaws[] = 0;
        [$totalMin, $totalMax] = [min($totalRaws), max($totalRaws)];
        $totalRange = $totalMax - $totalMin;

        // Same hybrid logic for per-subproject values.
        $allSpRaws = [];
        foreach ($workloads as $data) {
            foreach ($data['workload_per_subproject'] as $raw) {
                $allSpRaws[] = $raw;
            }
        }

        $allSpRaws[] = 0;
        [$spMin, $spMax] = [min($allSpRaws), max($allSpRaws)];
        $spRange = $spMax - $spMin;

        $normalize = static function (int $raw, int $min, int $range): float {
            if ($range === 0) {
                // All annotators have identical workload. If it is zero they are done → minimum;
                // if it is positive we cannot differentiate → midpoint.
                return $min === 0 ? 0.1 : 0.5;
            }

            return round(max(0.1, min(0.9, 0.1 + (($raw - $min) / $range) * 0.8)), 2);
        };

        $result = [];
        foreach ($annotatorIds as $id) {
            $data = $workloads[$id] ?? null;

            $perSubproject = array_map(
                fn (int $raw): float => $normalize($raw, $spMin, $spRange),
                $data['workload_per_subproject'] ?? [],
            );

            $result[$id] = [
                'total' => $normalize($data['total_workload'] ?? 0, $totalMin, $totalRange),
                'per_subproject' => $perSubproject,
            ];
        }

        return $result;
    }
}
