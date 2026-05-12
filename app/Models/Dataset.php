<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'is_available',
])]
/**
 * @property-read int $id
 * @property string $name
 * @property string|null $description
 * @property bool $is_available
 * @property-read Collection<int, User> $connectedManagers
 * @property-read int|null $instances_count
 */
class Dataset extends Model {
    /** @use HasFactory<DatasetFactory> */
    use HasFactory;

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /** @return HasMany<DatasetInstance, $this> */
    public function instances(): HasMany {
        return $this->hasMany(DatasetInstance::class);
    }

    /** @return BelongsToMany<User, $this> */
    public function connectedManagers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'dataset_user', 'dataset_id', 'user_id');
    }

    /** @return BelongsToMany<AnnotationTask, $this> */
    protected function connectedAnnotationTasks(): BelongsToMany {
        return $this->belongsToMany(AnnotationTask::class, 'dataset_annotation_tasks', 'dataset_id', 'annotation_task_id');
    }
}
