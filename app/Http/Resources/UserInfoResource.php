<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Enums\RolesEnum;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserInfoResource extends JsonResource {
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array {
        return [
            'user' => new UserResource($this->resource)->toArray($request),
            'permissions' => $this->resolvePermissions(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function resolvePermissions(): array {
        /** @var User $user */
        $user = $this->resource;

        $permissions = [];

        if ($user->hasRole([RolesEnum::ADMINISTRATOR->value, RolesEnum::USER_MANAGER->value])) {
            $permissions['dashboard'] = true;
        }

        return $permissions;
    }
}
