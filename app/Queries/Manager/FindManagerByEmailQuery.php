<?php

declare(strict_types=1);

namespace App\Queries\Manager;

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

final readonly class FindManagerByEmailQuery {
    /**
     * Finds an active or pending user with the ADMIN or ANNOTATION_MANAGER role by email.
     * Returns the full model (no column restriction) so it can be used as a mail notifiable.
     */
    public function get(string $email): ?User {
        return User::query()
            ->where('email', $email)
            ->whereIn('status', [StatusEnum::ACTIVE, StatusEnum::PENDING])
            ->whereHas('roles', fn (Builder $q) => $q->whereIn('name', [
                RolesEnum::ADMIN->value,
                RolesEnum::ANNOTATION_MANAGER->value,
            ]))
            ->first();
    }
}
