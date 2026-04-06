<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetInstanceElement extends Model {
    /** @use HasFactory<\Database\Factories\DatasetInstanceElementFactory> */
    use HasFactory;

    protected $fillable = [
        'index',
        'key',
        'value',
        'dataset_instance_id',
    ];
}
