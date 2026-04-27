<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use App\Enums\SubProjectPriorityEnum;
use Carbon\Carbon;
use Database\Factories\SubProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $project_id
 * @property string $name
 * @property ProjectStatusEnum $status
 * @property SubProjectPriorityEnum $priority
 * @property bool $flexible
 * @property bool $auto_submission
 * @property int $minimum_annotators
 * @property int $first_instance_index
 * @property int $last_instance_index
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $deadline_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Project $project
 */
#[Fillable([
    'project_id',
    'name',
    'status',
    'priority',
    'flexible',
    'auto_submission',
    'minimum_annotators',
    'first_instance_index',
    'last_instance_index',
    'scheduled_at',
    'deadline_at',
    'started_at',
    'completed_at',
])]
class SubProject extends Model {
    /** @use HasFactory<SubProjectFactory> */
    use HasFactory;

    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'status' => ProjectStatusEnum::class,
            'priority' => SubProjectPriorityEnum::class,
            'flexible' => 'boolean',
            'auto_submission' => 'boolean',
            'scheduled_at' => 'date',
            'deadline_at' => 'date',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }
}
