<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\TaskTagFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read int $id
 * @property string $name
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
#[Fillable(['name'])]
#[Table(name: 'task_tags')]
class TaskTag extends Model {
    /** @use HasFactory<TaskTagFactory> */
    use HasFactory;

    /** @return BelongsToMany<AnnotationTask, $this> */
    public function annotationTasks(): BelongsToMany {
        return $this->belongsToMany(
            AnnotationTask::class,
            'annotation_task_task_tag',
            'task_tag_id',
            'annotation_task_id',
        );
    }
}
