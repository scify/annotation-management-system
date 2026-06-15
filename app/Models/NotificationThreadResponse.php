<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationThreadResponseEnum;
use Carbon\Carbon;
use Database\Factories\NotificationThreadResponseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $notification_thread_id
 * @property NotificationThreadResponseEnum $response
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read NotificationThread $thread
 */
#[Fillable([
    'notification_thread_id',
    'response',
])]
class NotificationThreadResponse extends Model {
    /** @use HasFactory<NotificationThreadResponseFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<NotificationThread, $this>
     */
    public function thread(): BelongsTo {
        return $this->belongsTo(NotificationThread::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'response' => NotificationThreadResponseEnum::class,
        ];
    }
}
