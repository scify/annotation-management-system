<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $notification_thread_id
 * @property int|null $sender_user_id
 * @property int $recipient_user_id
 * @property string $body
 * @property bool $is_read
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read NotificationThread $thread
 * @property-read User|null $sender
 * @property-read User $recipient
 */
#[Fillable([
    'notification_thread_id',
    'sender_user_id',
    'recipient_user_id',
    'body',
    'is_read',
])]
class Notification extends Model {
    /** @use HasFactory<NotificationFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<NotificationThread, $this>
     */
    public function thread(): BelongsTo {
        return $this->belongsTo(NotificationThread::class, 'notification_thread_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function sender(): BelongsTo {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function recipient(): BelongsTo {
        return $this->belongsTo(User::class, 'recipient_user_id');
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'is_read' => 'boolean',
        ];
    }
}
