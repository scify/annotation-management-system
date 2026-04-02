<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotationInstanceElementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotationInstanceElement extends Model {
    /** @use HasFactory<AnnotationInstanceElementFactory> */
    use HasFactory;
}
