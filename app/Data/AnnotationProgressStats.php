<?php

declare(strict_types=1);

namespace App\Data;

final readonly class AnnotationProgressStats {
    public float $submittedPct;

    public float $submittedAndPendingPct;

    public function __construct(
        public int $submittedCount,
        public int $notAnnotatedCount,
        public int $pendingCount,
    ) {
        $total = $submittedCount + $notAnnotatedCount + $pendingCount;
        $this->submittedPct = $total > 0 ? round($submittedCount / $total * 100, 2) : 0.0;
        $this->submittedAndPendingPct = $total > 0 ? round(($submittedCount + $pendingCount) / $total * 100, 2) : 0.0;
    }

    /**
     * @param  array{pending_count: int, submitted_count: int, not_annotated_count: int}  $counts
     */
    public static function fromCounts(array $counts): self {
        return new self(
            submittedCount: $counts['submitted_count'],
            notAnnotatedCount: $counts['not_annotated_count'],
            pendingCount: $counts['pending_count'],
        );
    }
}
