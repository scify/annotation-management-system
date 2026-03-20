<?php

declare(strict_types=1);

namespace App\Console\Commands\Api;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\PersonalAccessToken;

class TokenRevokeCommand extends Command {
    protected $signature = 'api:token:revoke {tokenId : The ID of the token to revoke}';

    protected $description = 'Revoke a personal access token by its ID';

    public function handle(): int {
        $tokenId = (string) $this->argument('tokenId');

        $token = PersonalAccessToken::query()->find($tokenId);

        if ($token === null) {
            $this->error('No token found with ID: ' . $tokenId);

            return self::FAILURE;
        }

        $tokenName = $token->name;

        /** @var Model $owner */
        $owner = $token->tokenable;
        $ownerEmail = $owner instanceof User ? $owner->email : 'unknown';

        $token->delete();

        $this->info(sprintf('Token "%s" (ID: %s) for %s has been revoked.', $tokenName, $tokenId, $ownerEmail));

        return self::SUCCESS;
    }
}
