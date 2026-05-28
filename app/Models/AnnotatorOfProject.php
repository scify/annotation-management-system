<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotatorOfProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Table;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $project_id
 * @property int $user_id
 * @property bool $can_flag
 * @property-read Project $project
 * @property-read User $user
 */
#[Fillable([
    'project_id',
    'user_id',
    'can_flag',
])]
#[Table(name: 'annotator_of_project')]
class AnnotatorOfProject extends Model {
    /** @use HasFactory<AnnotatorOfProjectFactory> */
    use HasFactory;

    /** @var array<string, string> */
    protected $casts = ['can_flag' => 'boolean'];

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
