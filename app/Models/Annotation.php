<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $annotation_assignment_id
 * @property int $dataset_instance_id
 * @property int $index
 * @property array<string, mixed> $annotations
 * @property bool $pending
 */
#[Fillable([
    'annotation_assignment_id',
    'dataset_instance_id',
    'index',
    'annotations',
    'pending',
])]
class Annotation extends Model {
    /** @use HasFactory<AnnotationFactory> */
    use HasFactory;

    protected $casts = [
        'annotations' => 'array',
        'pending' => 'boolean',
    ];
}
