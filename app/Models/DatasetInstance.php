<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetInstanceFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DatasetInstance extends Model {
    /** @use HasFactory<DatasetInstanceFactory> */
    use HasFactory;

    protected $fillable = [
        'index',
        'dataset_id',
        'content',
    ];

    protected $casts = [
        'content' => 'array',
    ];
}
