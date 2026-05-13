<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotatorOfManagerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'manager_id',
    'annotator_id',
])]
class AnnotatorOfManager extends Model {
    /** @use HasFactory<AnnotatorOfManagerFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<User, $this>
     */
    public function manager(): BelongsTo {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function annotator(): BelongsTo {
        return $this->belongsTo(User::class, 'annotator_id');
    }
}
