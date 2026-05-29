<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfidenceEnum;
use Database\Factories\AnnotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $annotation_assignment_id
 * @property int $dataset_instance_id
 * @property int $index
 * @property array<string, mixed>|null $annotations
 * @property bool $pending
 * @property bool $is_flagged
 * @property ConfidenceEnum|null $confidence
 * @property int|null $last_edited_by
 * @property-read User|null $lastEditedBy
 */
#[Fillable([
    'annotation_assignment_id',
    'dataset_instance_id',
    'index',
    'annotations',
    'pending',
    'is_flagged',
    'confidence',
    'last_edited_by',
])]
class Annotation extends Model {
    /** @use HasFactory<AnnotationFactory> */
    use HasFactory;

    protected $casts = [
        'annotations' => 'array',
        'pending' => 'boolean',
        'is_flagged' => 'boolean',
        'confidence' => ConfidenceEnum::class,
    ];

    public function isAnnotated(): bool {
        return $this->annotations !== null && ! $this->pending;
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function lastEditedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
