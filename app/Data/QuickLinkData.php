<?php

declare(strict_types=1);

namespace App\Data;

final readonly class QuickLinkData {
    public function __construct(
        public string $label,
        public string $url,
        public ?int $annotationId = null,
    ) {}
}
