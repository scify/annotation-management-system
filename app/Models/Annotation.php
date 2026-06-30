<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfidenceEnum;
use Database\Factories\AnnotationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $annotation_assignment_id
 * @property int $dataset_instance_id
 * @property int $project_instance_index index as ordered by the Project (dataset-level shuffle)
 * @property int $annotator_instance_index index as ordered for this annotator (equals project_instance_index unless per-annotator shuffle is active)
 * @property array<string, mixed>|null $annotations
 * @property bool $pending
 * @property int|null $flag_notification_thread_id
 * @property int|null $message_to_managers_notification_thread_id
 * @property ConfidenceEnum|null $confidence
 * @property int|null $last_edited_by
 * @property-read DatasetInstance $datasetInstance
 * @property-read User|null $lastEditedBy
 * @property-read NotificationThread|null $flagNotificationThread
 */
#[Fillable([
    'annotation_assignment_id',
    'dataset_instance_id',
    'project_instance_index',
    'annotator_instance_index',
    'annotations',
    'pending',
    'flag_notification_thread_id',
    'message_to_managers_notification_thread_id',
    'confidence',
    'last_edited_by',
])]
class Annotation extends Model {
    /** @use HasFactory<AnnotationFactory> */
    use HasFactory;

    protected $casts = [
        'annotations' => 'array',
        'pending' => 'boolean',
        'confidence' => ConfidenceEnum::class,
    ];

    public function isFlagged(): bool {
        return $this->flag_notification_thread_id !== null && $this->annotations === null;
    }

    public function isAnnotated(): bool {
        return $this->annotations !== null && ! $this->pending;
    }

    /** @return BelongsTo<DatasetInstance, $this> */
    public function datasetInstance(): BelongsTo {
        return $this->belongsTo(DatasetInstance::class);
    }

    /** @return BelongsTo<NotificationThread, $this> */
    public function flagNotificationThread(): BelongsTo {
        return $this->belongsTo(NotificationThread::class, 'flag_notification_thread_id');
    }

    /** @return BelongsTo<User, $this> */
    public function lastEditedBy(): BelongsTo {
        return $this->belongsTo(User::class, 'last_edited_by');
    }
}
