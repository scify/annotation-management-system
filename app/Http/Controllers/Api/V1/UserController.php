<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\UserInfoResource;
use App\Models\User;

class UserController extends BaseApiController {
    public function userInfo(): UserInfoResource {
        /** @var User $user */
        $user = auth()->user();

        return new UserInfoResource($user);
    }
}
