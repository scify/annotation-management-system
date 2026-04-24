<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\Carbon;
use Database\Factories\UserFactory;
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
 * @property-read string $name
 * @property-read string $username
 * @property-read string $email
 * @property-read string $password
 * @property-read string $is_active
 * @property-read string|null $remember_token
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 * @property-read Carbon|null $deleted_at
 * @property-read string|null $role
 * @property-read Collection|Role[] $roles
 */
class User extends Authenticatable {
    use HasApiTokens;

    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasRoles;
    use Notifiable;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'is_active',
        'deleted_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $appends = [
        'role',
    ];

    protected $with = [
        'roles',
    ];

    public function annotationTasks(): HasMany {
        return $this->hasMany(AnnotationTask::class);
    }

    public function projects(): HasMany {
        return $this->hasMany(Project::class, 'owner_user_id');
    }

    public function managedProjects(): BelongsToMany {
        return $this->belongsToMany(Project::class, 'project_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function relations(): HasMany {
        return $this->hasMany(UserRelation::class, 'user_id');
    }

    public function relatedToMe(): HasMany {
        return $this->hasMany(UserRelation::class, 'related_user_id');
    }

    public function relatedUsers(): BelongsToMany {
        return $this->belongsToMany(
            self::class,
            'user_relations',
            'user_id',
            'related_user_id'
        )->withPivot('relation_type');
    }

    public function relatedByUsers(): BelongsToMany {
        return $this->belongsToMany(
            self::class,
            'user_relations',
            'related_user_id',
            'user_id'
        )->withPivot('relation_type');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'is_active' => 'boolean',
            'password' => 'hashed',
            'deleted_at' => 'datetime',
        ];
    }

    protected function getRoleAttribute(): ?string {
        return $this->roles->first()?->name;
    }
}
