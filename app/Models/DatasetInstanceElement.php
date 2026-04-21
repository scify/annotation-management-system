<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetInstanceElementFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetInstanceElement extends Model {
    /** @use HasFactory<DatasetInstanceElementFactory> */
    use HasFactory;

    protected $fillable = [
        'index',
        'key',
        'value',
        'dataset_instance_id',
    ];
}
