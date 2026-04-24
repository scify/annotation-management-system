<?php

declare(strict_types=1);

namespace App\Services\User;

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\SubProject;
use App\Models\User;
use Illuminate\Support\Collection;

class UserService {
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): User {
        $role = $data['role'] ?? RolesEnum::ANNOTATOR->value;
        unset($data['role']);

        $user = User::query()->create($data);
        $user->syncRoles([$role]);

        return $user;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(User $user, array $data): User {
        $role = $data['role'] ?? $user->roles->first()?->name;
        unset($data['role']);

        $user->update($data);
        $user->syncRoles([$role]);

        return $user;
    }

    public function delete(User $user): ?bool {
        return $user->delete();
    }

    public function restore(User $user): User {
        $user->restore();

        return $user;
    }

    public function getWorkload(User $user): ?float {
        if (! $user->hasRole(RolesEnum::ANNOTATOR)) {
            return null;
        }

        $subProjects = SubProject::query()
            ->where('status', ProjectStatusEnum::IN_PROGRESS)
            ->whereIn('id', function ($query) use ($user): void {
                $query->select('sub_project_id')
                    ->from('annotation_assignments')
                    ->where('user_id', $user->id);
            })
            ->with('project.annotationTask')
            ->get();

        $annotationAssignments = AnnotationAssignment::query()
            ->where('user_id', $user->id)
            ->whereIn('sub_project_id', $subProjects->pluck('id'))
            ->get();

        $annotationCountsByAssignment = Annotation::query()
            ->whereIn('annotation_assignment_id', $annotationAssignments->pluck('id'))
            ->selectRaw('annotation_assignment_id, COUNT(*) as count')
            ->groupBy('annotation_assignment_id')
            ->pluck('count', 'annotation_assignment_id');

        $assignmentsBySubProject = $annotationAssignments->keyBy('sub_project_id');

        $sum_of_effort = 0.0;
        $sum_of_work_done = 0.0;
        foreach ($subProjects as $subProject) {
            /** @var SubProject $subProject */
            $weight = $subProject->project->annotationTask->weight;
            $effort = ($subProject->last_instance_index - $subProject->first_instance_index) * $weight;
            $sum_of_effort += $effort;
            $assignment = $assignmentsBySubProject->get($subProject->id);
            $work_done = $assignment instanceof AnnotationAssignment
                ? (int) $annotationCountsByAssignment->get($assignment->getKey(), 0)
                : 0;
            $sum_of_work_done += $work_done * $weight;
        }

        return 0.0;
    }

    public function findByEmail(string $email): ?User {
        return User::query()->where('email', $email)->first();
    }

    /**
     * Get users based on filters
     *
     * @return Collection<int, User>
     */
    public function getUsers(
        ?string $search = null,
    ): Collection {
        return User::query()
            ->when($search, function ($query, $search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('name', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('email', 'like', sprintf('%%%s%%', $search))
                        ->orWhere('username', 'like', sprintf('%%%s%%', $search));
                });
            })
            ->withTrashed()
            ->with('roles')
            ->get();
    }

    /**
     * Get the roles for the form.
     *
     * @return Collection<int, array{name: string, label: string}>
     *
     * @phpstan-return Collection<int, array{name: string, label: string}>
     */
    public function getRolesForForm(): Collection {
        /** @var User $user */
        $user = auth()->user();

        // Annotation managers can assign annotators and other annotation managers, but not admins
        $cases = $user->hasRole(RolesEnum::ADMIN->value)
            ? RolesEnum::cases()
            : [RolesEnum::ANNOTATION_MANAGER, RolesEnum::ANNOTATOR];

        return collect($cases)->map(fn (RolesEnum $rolesEnum): array => [
            'name' => $rolesEnum->value,
            'label' => 'roles.' . $rolesEnum->value,
        ])->values();
    }
}
