<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\WorkloadFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workload extends Model {
    /** @use HasFactory<WorkloadFactory> */
    use HasFactory;
}
