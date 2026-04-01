<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserRelationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserRelation extends Model {
    /** @use HasFactory<UserRelationFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'related_user_id',
        'relation_type',
    ];
}
