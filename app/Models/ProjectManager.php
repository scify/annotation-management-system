<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ProjectManagerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $project_id
 * @property int $user_id
 * @property bool $accepted
 * @property-read Project $project
 * @property-read User $user
 */
#[Fillable([
    'project_id',
    'user_id',
    'accepted',
])]
#[Table(name: 'project_managers')]
class ProjectManager extends Model {
    /** @use HasFactory<ProjectManagerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
