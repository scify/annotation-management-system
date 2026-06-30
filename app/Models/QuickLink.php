<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\RolesEnum;
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
 * @property int|null $annotation_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read NotificationThread $thread
 * @property-read Annotation|null $annotation
 */
#[Fillable([
    'notification_thread_id',
    'label',
    'url',
    'annotation_id',
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

    /**
     * @return BelongsTo<Annotation, $this>
     */
    public function annotation(): BelongsTo {
        return $this->belongsTo(Annotation::class);
    }

    public function getLabel(User $user): string {
        if (! str_ends_with($this->label, '#') || $this->annotation_id === null) {
            return $this->label;
        }

        $annotation = $this->annotation;

        if ($annotation === null) {
            return $this->label;
        }

        if ($user->hasRole(RolesEnum::ANNOTATOR)) {
            return $this->label . $annotation->annotator_instance_index;
        }

        return $this->label . $annotation->datasetInstance->index;
    }
}
