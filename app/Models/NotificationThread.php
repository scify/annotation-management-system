<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationThreadTypeEnum;
use Carbon\Carbon;
use Database\Factories\NotificationThreadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property-read int $id
 * @property NotificationThreadTypeEnum $type
 * @property bool|null $is_accepted
 * @property bool|null $is_rejected
 * @property string|null $title
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Collection<int, Notification> $notifications
 * @property-read Collection<int, QuickLink> $quickLinks
 */
#[Fillable([
    'type',
    'is_accepted',
    'is_rejected',
    'title',
])]
class NotificationThread extends Model {
    /** @use HasFactory<NotificationThreadFactory> */
    use HasFactory;

    /**
     * @return HasMany<Notification, $this>
     */
    public function notifications(): HasMany {
        return $this->hasMany(Notification::class);
    }

    /**
     * @return HasMany<QuickLink, $this>
     */
    public function quickLinks(): HasMany {
        return $this->hasMany(QuickLink::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'type' => NotificationThreadTypeEnum::class,
            'is_accepted' => 'boolean',
            'is_rejected' => 'boolean',
        ];
    }
}
