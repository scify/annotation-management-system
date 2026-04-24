<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotationAssignmentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotationAssignment extends Model {
    /** @use HasFactory<AnnotationAssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'sub_project_id',
        'shuffling_array',
    ];

    protected $casts = [];
}
