<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AnnotationSessionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $annotation_assignment_id
 * @property Carbon $started_timestamp
 * @property Carbon|null $ended_timestamp
 * @property int $session_annotations_count
 * @property int|null $next_annotation_id
 * @property-read AnnotationAssignment $annotationAssignment
 * @property-read Annotation|null $nextAnnotation
 */
#[Fillable([
    'annotation_assignment_id',
    'started_timestamp',
    'ended_timestamp',
    'session_annotations_count',
    'next_annotation_id',
])]
class AnnotationSession extends Model {
    /** @use HasFactory<AnnotationSessionFactory> */
    use HasFactory;

    protected $casts = [
        'started_timestamp' => 'datetime',
        'ended_timestamp' => 'datetime',
        'session_annotations_count' => 'integer',
    ];

    /** @return BelongsTo<AnnotationAssignment, $this> */
    public function annotationAssignment(): BelongsTo {
        return $this->belongsTo(AnnotationAssignment::class);
    }

    /** @return BelongsTo<Annotation, $this> */
    public function nextAnnotation(): BelongsTo {
        return $this->belongsTo(Annotation::class, 'next_annotation_id');
    }
}
