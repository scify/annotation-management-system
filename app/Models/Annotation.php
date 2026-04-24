<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Annotation extends Model {
    /** @use HasFactory<AnnotationFactory> */
    use HasFactory;

    protected $fillable = [
        'annotation_assignment_id',
        'dataset_instance_id',
        'index',
        'annotations',
    ];

    protected $casts = [
        'annotations' => 'array',
    ];
}
