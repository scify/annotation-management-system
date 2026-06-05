<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\PasswordCompositionEnum;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read int $id
 * @property int $min_length
 * @property PasswordCompositionEnum $composition_mode
 * @property bool $mixed_case_required
 * @property-read CarbonImmutable $created_at
 * @property-read CarbonImmutable $updated_at
 */
#[Fillable(['min_length', 'composition_mode', 'mixed_case_required'])]
class AnnotatorPasswordPolicy extends Model {
    /** @var array<string, string> */
    protected $casts = [
        'min_length' => 'integer',
        'composition_mode' => PasswordCompositionEnum::class,
        'mixed_case_required' => 'boolean',
    ];
}
