<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AnnotationTaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property string $title
 * @property string $short_description
 * @property string|null $description
 * @property string|null $guidelines_url
 * @property int $weight
 * @property array<int, array{id: int, question: string, answers: list<string>, parameters: list<string>}>|null $customization_options
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read Collection<int, TaskTag> $tags
 * @property-read Collection<int, Dataset> $datasets
 */
#[Fillable([
    'title',
    'short_description',
    'description',
    'guidelines_url',
    'weight',
    'customization_options',
])]
class AnnotationTask extends Model {
    /** @use HasFactory<AnnotationTaskFactory> */
    use HasFactory;

    use SoftDeletes;

    /** @return BelongsToMany<Dataset, $this> */
    public function datasets(): BelongsToMany {
        return $this->belongsToMany(Dataset::class, 'dataset_annotation_tasks', 'annotation_task_id', 'dataset_id');
    }

    /** @return BelongsToMany<User, $this> */
    public function connectedUsers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'annotation_task_user', 'annotation_task_id', 'user_id');
    }

    /** @return BelongsToMany<TaskTag, $this> */
    public function tags(): BelongsToMany {
        return $this->belongsToMany(
            TaskTag::class,
            'annotation_task_tag',
            'annotation_task_id',
            'task_tag_id',
        );
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'customization_options' => 'array',
        ];
    }
}
