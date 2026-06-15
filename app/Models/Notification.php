<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\NotificationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property int $notification_thread_id
 * @property int|null $sender_user_id
 * @property string $body
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read NotificationThread $thread
 * @property-read User|null $sender
 * @property-read Collection<int, ThreadMember> $members
 */
#[Fillable([
    'notification_thread_id',
    'sender_user_id',
    'body',
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
     * @return HasMany<ThreadMember, $this>
     */
    public function members(): HasMany {
        return $this->hasMany(ThreadMember::class);
    }
}
