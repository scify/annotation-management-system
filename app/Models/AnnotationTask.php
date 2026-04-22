<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\AnnotationTaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property-read int $id
 * @property string $title
 * @property string|null $description
 * @property string|null $guidelines_url
 * @property int $weight
 * @property array<int, array{id: int, question: string, answers: list<string>, parameters: list<string>}>|null $customization_options
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 */
class AnnotationTask extends Model {
    /** @use HasFactory<AnnotationTaskFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'description',
        'guidelines_url',
        'weight',
        'customization_options',
    ];

    public function connectedUsers(): BelongsToMany {
        return $this->belongsToMany(User::class, 'annotation_task_user', 'annotation_task_id', 'user_id');
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'customization_options' => 'array',
        ];
    }
}
