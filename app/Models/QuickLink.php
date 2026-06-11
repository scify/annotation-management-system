<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\QuickLinkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $notification_thread_id
 * @property string $label
 * @property string $url
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read NotificationThread $thread
 */
#[Fillable([
    'notification_thread_id',
    'label',
    'url',
])]
class QuickLink extends Model {
    /** @use HasFactory<QuickLinkFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<NotificationThread, $this>
     */
    public function thread(): BelongsTo {
        return $this->belongsTo(NotificationThread::class, 'notification_thread_id');
    }
}
