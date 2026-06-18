<?php

declare(strict_types=1);

namespace App\Data;

final readonly class ProjectMemberContextData {
    public function __construct(
        public int $projectId,
        public int $targetUserId,
    ) {}
}
