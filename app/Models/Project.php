<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ProjectStatusEnum;
use Carbon\Carbon;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Date;

/**
 * @property-read int $id
 * @property string $name
 * @property string $description
 * @property int $owner_user_id
 * @property int $annotation_task_id
 * @property int $dataset_id
 * @property ProjectStatusEnum $status
 * @property bool $restricted_visibility
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $deadline_at
 * @property Carbon|null $started_at
 * @property Carbon|null $completed_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read User $owner
 * @property-read AnnotationTask $annotationTask
 * @property-read Dataset $dataset
 * @property-read Collection<int, SubProject> $subProjects
 * @property-read Collection<int, User> $managers
 * @property-read bool $is_delayed_to_start
 * @property-read bool $is_delayed_to_end
 */
class Project extends Model {
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $appends = ['is_delayed_to_start', 'is_delayed_to_end'];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'description',
        'owner_user_id',
        'annotation_task_id',
        'dataset_id',
        'status',
        'restricted_visibility',
        'scheduled_at',
        'deadline_at',
        'started_at',
        'completed_at',
    ];

    public function owner(): BelongsTo {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function annotationTask(): BelongsTo {
        return $this->belongsTo(AnnotationTask::class);
    }

    public function dataset(): BelongsTo {
        return $this->belongsTo(Dataset::class);
    }

    public function subProjects(): HasMany {
        return $this->hasMany(SubProject::class);
    }

    public function managers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'status' => ProjectStatusEnum::class,
            'restricted_visibility' => 'boolean',
            'scheduled_at' => 'datetime',
            'deadline_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    protected function getIsDelayedToStartAttribute(): bool {
        $now = Date::now();

        return $this->scheduled_at !== null && $now->gt($this->scheduled_at) && $this->status === ProjectStatusEnum::PENDING;
    }

    protected function getIsDelayedToEndAttribute(): bool {
        $now = Date::now();

        return $this->deadline_at !== null && $now->gt($this->deadline_at) && $this->status !== ProjectStatusEnum::COMPLETED;
    }
}
