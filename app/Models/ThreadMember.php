<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\ThreadMemberFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $notification_id
 * @property int $user_id
 * @property bool $is_read
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Notification $notification
 * @property-read User $user
 */
#[Fillable([
    'notification_id',
    'user_id',
    'is_read',
])]
class ThreadMember extends Model {
    /** @use HasFactory<ThreadMemberFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Notification, $this>
     */
    public function notification(): BelongsTo {
        return $this->belongsTo(Notification::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
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
