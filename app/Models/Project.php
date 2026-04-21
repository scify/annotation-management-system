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
 * @property Carbon|null $scheduled_at
 * @property Carbon|null $deadline_at
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read User $owner
 * @property-read AnnotationTask $annotationTask
 * @property-read Dataset $dataset
 * @property-read Collection<int, SubProject> $subProjects
 * @property-read Collection<int, User> $managers
 * @property-read bool $is_delayed
 */
class Project extends Model {
    /** @use HasFactory<ProjectFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $appends = ['is_delayed'];

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
        'scheduled_at',
        'deadline_at',
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

    protected function getIsDelayedAttribute(): bool {
        $now = Date::now();
        $delayed_to_start = false;
        if ($this->scheduled_at !== null && $now->gt($this->scheduled_at) && $this->status === ProjectStatusEnum::PENDING) {
            $delayed_to_start = true;
        }

        $delayed_to_end = false;
        if ($this->deadline_at !== null && $now->gt($this->deadline_at) && $this->status !== ProjectStatusEnum::COMPLETED) {
            $delayed_to_end = true;
        }

        return $delayed_to_start || $delayed_to_end;
    }
}
