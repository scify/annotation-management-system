<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\DatasetInstanceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable([
    'index',
    'dataset_id',
    'content',
])]
class DatasetInstance extends Model {
    /** @use HasFactory<DatasetInstanceFactory> */
    use HasFactory;

    protected $casts = [
        'content' => 'array',
    ];
}
