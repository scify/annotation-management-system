<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ConfidenceEnum;
use Database\Factories\ConfidenceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $annotation_id
 * @property ConfidenceEnum $value
 * @property-read Annotation $annotation
 */
#[Fillable([
    'annotation_id',
    'value',
])]
class Confidence extends Model {
    /** @use HasFactory<ConfidenceFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Annotation, $this>
     */
    public function annotation(): BelongsTo {
        return $this->belongsTo(Annotation::class);
    }

    /**
     * @return array<string, string|class-string>
     */
    protected function casts(): array {
        return [
            'value' => ConfidenceEnum::class,
        ];
    }
}
