<?php

declare(strict_types=1);

namespace App\Services\SubProject;

use App\Models\SubProject;

class SubProjectService {
    public function getWorkload(SubProject $subProject): float {
        return 0.5;
    }

    public function getProgress(SubProject $subProject): float {
        return 0.5;
    }
}
