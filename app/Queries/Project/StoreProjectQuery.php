<?php

declare(strict_types=1);

namespace App\Queries\Project;

use App\Enums\ProjectStatusEnum;
use App\Models\Project;

final readonly class StoreProjectQuery {
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, int $ownerUserId): Project {
        /** @var Project */
        return Project::query()->create([
            'name' => $data['name'],
            'owner_user_id' => $ownerUserId,
            'annotation_task_id' => $data['annotation_task_id'],
            'dataset_id' => $data['dataset_id'],
            'status' => ProjectStatusEnum::PENDING,
            'is_instance_shuffled' => $data['is_instance_shuffled'],
            'annotation_task_configuration' => $data['annotation_task_configuration'] ?? null,
            'restricted_visibility' => $data['restricted_visibility'],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'deadline_at' => $data['deadline_at'] ?? null,
        ]);
    }
}
