<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\AnnotationInstanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnnotationInstance extends Model {
    /** @use HasFactory<AnnotationInstanceFactory> */
    use HasFactory;
}
