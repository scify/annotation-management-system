<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Dataset extends Model {
    /** @use HasFactory<DatasetFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'desciption',
        'is_available',
    ];

    /**
     * @return BelongsToMany<AnnotationTask, $this>
     */
    protected function connectedAnnotationTasks(): BelongsToMany {
        return $this->belongsToMany(AnnotationTask::class, 'dataset_annotation_tasks', 'dataset_id', 'annotation_task_id');
    }
}
