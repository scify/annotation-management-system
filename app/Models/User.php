<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\StatusEnum;
use Carbon\Carbon;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Appends;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property string $name
 * @property string $username
 * @property string|null $email
 * @property string|null $password
 * @property StatusEnum $status
 * @property string|null $remember_token
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read string|null $role
 * @property-read Collection|Role[] $roles
 */
#[Appends([
    'role',
])]
#[Fillable([
    'name',
    'username',
    'email',
    'password',
    'status',
    'deleted_at',
])]
#[Hidden([
    'password',
    'remember_token',
])]
class User extends Authenticatable {
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    protected $with = [
        'roles',
    ];

    /** @return HasMany<AnnotationTask, $this> */
    public function annotationTasks(): HasMany {
        return $this->hasMany(AnnotationTask::class);
    }

    /** @return BelongsToMany<Dataset, $this> */
    public function connectedDatasets(): BelongsToMany {
        return $this->belongsToMany(Dataset::class, 'dataset_user', 'user_id', 'dataset_id');
    }

    /** @return HasMany<Project, $this> */
    public function projects(): HasMany {
        return $this->hasMany(Project::class, 'owner_user_id');
    }

    /** @return BelongsToMany<Project, $this> */
    public function managedProjects(): BelongsToMany {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    /** @return HasMany<ProjectManager, $this> */
    public function projectManagements(): HasMany {
        return $this->hasMany(ProjectManager::class, 'user_id');
    }

    /** @return HasMany<AnnotatorOfManager, $this> */
    public function managedAnnotators(): HasMany {
        return $this->hasMany(AnnotatorOfManager::class, 'manager_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'status' => StatusEnum::class,
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    protected function getRoleAttribute(): ?string {
        return $this->roles->first()?->name;
    }
}
