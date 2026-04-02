<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model {
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;
}
