<?php

declare(strict_types=1);

namespace App\Queries;

use App\Models\User;
use Illuminate\Support\Collection;

final readonly class GetUsersQuery {
    /**
     * @return Collection<int, User>
     */
    public function get(?string $search = null): Collection {
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
}
