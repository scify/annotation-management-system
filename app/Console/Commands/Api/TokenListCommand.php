<?php

declare(strict_types=1);

namespace App\Console\Commands\Api;

use App\Models\User;
use Illuminate\Console\Command;
use Laravel\Sanctum\PersonalAccessToken;

class TokenListCommand extends Command {
    protected $signature = 'api:token:list {email : The email address of the user}';

    protected $description = 'List personal access tokens for a user';

    public function handle(): int {
        $email = (string) $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error('No active user found with email: ' . $email);

            return self::FAILURE;
        }

        $tokens = $user->tokens;

        if ($tokens->isEmpty()) {
            $this->info(sprintf('User <%s> has no personal access tokens.', $email));

            return self::SUCCESS;
        }

        $this->table(
            ['ID', 'Name', 'Abilities', 'Last Used', 'Created At'],
            $tokens->map(function (PersonalAccessToken $token): array {
                $lastUsed = $token->last_used_at;
                $createdAt = $token->created_at;

                return [
                    $token->id,
                    $token->name,
                    implode(', ', $token->abilities ?? []),
                    $lastUsed !== null ? $lastUsed->toDateTimeString() : 'Never',
                    $createdAt !== null ? $createdAt->toDateTimeString() : 'N/A',
                ];
            })->toArray(),
        );

        return self::SUCCESS;
    }
}
