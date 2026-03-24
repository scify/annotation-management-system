<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AnnotationTask extends Model {
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'guidelines_url',
        'weight',
        'deleted_at',
    ];

    protected function connectedUsers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'annotation_tasks_users', 'annotation_task_id', 'user_id');
    }
}
