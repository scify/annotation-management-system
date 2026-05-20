<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $new_index
 * @property int $project_id
 * @property int $old_index
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Project $project
 */
#[Fillable([
    'new_index',
    'project_id',
    'old_index',
])]
class InstanceShuffleMapper extends Model {
    use HasFactory;

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
