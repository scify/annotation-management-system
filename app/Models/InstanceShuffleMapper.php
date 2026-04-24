<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read int $id
 * @property int $new_index
 * @property int $dataset_instance_id
 * @property int $project_id
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read DatasetInstance $datasetInstance
 * @property-read Project $project
 */
class InstanceShuffleMapper extends Model {
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'new_index',
        'dataset_instance_id',
        'project_id',
    ];

    /**
     * @return BelongsTo<DatasetInstance, $this>
     */
    public function datasetInstance(): BelongsTo {
        return $this->belongsTo(DatasetInstance::class);
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo {
        return $this->belongsTo(Project::class);
    }
}
