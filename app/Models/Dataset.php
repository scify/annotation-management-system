<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'name',
    'description',
    'is_available',
])]
class Dataset extends Model {
    /** @use HasFactory<DatasetFactory> */
    use HasFactory;

    protected $casts = [
        'is_available' => 'boolean',
    ];

    /**
     * @return BelongsToMany<AnnotationTask, $this>
     */
    protected function connectedAnnotationTasks(): BelongsToMany {
        return $this->belongsToMany(AnnotationTask::class, 'dataset_annotation_tasks', 'dataset_id', 'annotation_task_id');
    }
}
