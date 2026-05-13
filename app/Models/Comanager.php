<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\ComanagerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'user_id',
])]
class Comanager extends Model {
    /** @use HasFactory<ComanagerFactory> */
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
