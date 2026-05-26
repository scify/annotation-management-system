<?php

declare(strict_types=1);

namespace App\Services\Annotation;

use App\Services\User\UserService;
use Illuminate\Support\Facades\DB;

readonly class WorkloadService {
    private const int SUBPROJECT_INSTANCE_CEILING = 1000;

    private const int SUBPROJECT_INSTANCE_FLOOR = 100;

    private const int FALLBACK_AVG_WEIGHT = 3;

    public function __construct(private UserService $userService) {}

    /**
     * Returns min-max normalized workloads ([0.1, 0.9]) for each annotator ID — both
     * the total remaining workload and the remaining workload per subproject.
     *
     * Falls back to 0.5 when there is no data or no variance across the annotator set.
     *
     * @param  array<int, int>  $annotatorIds
     *
     * @return array<int, array{total: float, per_subproject: array<int, float>}>
     */
    public function computeNormalizedWorkloads(array $annotatorIds): array {
        if ($annotatorIds === []) {
            return [];
        }

        $workloads = $this->userService->getWorkloads($annotatorIds);

        // Normalize totals across the annotator set.
        $totalRaws = array_map(fn (array $w): int => $w['total_workload'], $workloads);
        [$totalMin, $totalMax] = $totalRaws !== [] ? [min($totalRaws), max($totalRaws)] : [0, 0];
        $totalRange = $totalMax - $totalMin;

        // Collect all (annotator × subproject) raw values — use append to keep duplicates
        // across annotators that share a subproject.
        $allSpRaws = [];
        foreach ($workloads as $data) {
            foreach ($data['workload_per_subproject'] as $raw) {
                $allSpRaws[] = $raw;
            }
        }

        // Anchor the scale with system-derived sentinels so that a small or skewed batch
        // of subprojects does not collapse the normalization range.
        $allSpRaws[] = $this->computeSubprojectWorkloadMin();
        $allSpRaws[] = $this->computeSubprojectWorkloadMax();

        [$spMin, $spMax] = [min($allSpRaws), max($allSpRaws)];
        $spRange = $spMax - $spMin;

        $normalize = static function (int $raw, int $min, int $range): float {
            if ($range === 0) {
                return 0.5;
            }

            return round(max(0.1, min(0.9, 0.1 + (($raw - $min) / $range) * 0.8)), 2);
        };

        $result = [];
        foreach ($annotatorIds as $id) {
            $intId = (int) $id;
            $data = $workloads[$intId] ?? null;

            $perSubproject = array_map(
                fn (int $raw): float => $normalize($raw, $spMin, $spRange),
                $data['workload_per_subproject'] ?? [],
            );

            $result[$intId] = [
                'total' => $normalize($data['total_workload'] ?? 0, $totalMin, $totalRange),
                'per_subproject' => $perSubproject,
            ];
        }

        return $result;
    }

    /**
     * Ceiling sentinel: largest dataset instance count, floored at SUBPROJECT_INSTANCE_CEILING,
     * scaled by the system average annotation task weight.
     */
    private function computeSubprojectWorkloadMax(): int {
        $avgWeight = $this->computeAvgAnnotationTaskWeight();

        $maxSize = DB::table('datasets')->max('size');
        $largestCount = is_numeric($maxSize) ? (int) $maxSize : 0;

        $effectiveCount = max($largestCount, self::SUBPROJECT_INSTANCE_CEILING);

        return (int) round($effectiveCount * $avgWeight);
    }

    /**
     * Floor sentinel: smallest dataset instance count, capped at SUBPROJECT_INSTANCE_FLOOR,
     * scaled by the system average annotation task weight.
     */
    private function computeSubprojectWorkloadMin(): int {
        $avgWeight = $this->computeAvgAnnotationTaskWeight();

        $minSize = DB::table('datasets')->min('size');
        $shortestCount = is_numeric($minSize) ? (int) $minSize : 0;

        $effectiveCount = min($shortestCount, self::SUBPROJECT_INSTANCE_FLOOR);

        return (int) round($effectiveCount * $avgWeight);
    }

    private function computeAvgAnnotationTaskWeight(): float {
        return (float) (DB::table('annotation_tasks')->avg('weight') ?? self::FALLBACK_AVG_WEIGHT);
    }
}
