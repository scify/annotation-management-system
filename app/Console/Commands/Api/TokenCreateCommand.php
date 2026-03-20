<?php

declare(strict_types=1);

namespace App\Console\Commands\Api;

use App\Models\User;
use Illuminate\Console\Command;

class TokenCreateCommand extends Command {
    protected $signature = 'api:token:create
                            {email : The email address of the user to issue a token for}
                            {--name=default : Name for the token}
                            {--abilities=* : Abilities to grant (defaults to wildcard)}';

    protected $description = 'Create a personal access token for a user (for API clients)';

    public function handle(): int {
        $email = (string) $this->argument('email');

        $user = User::query()->where('email', $email)->first();

        if ($user === null) {
            $this->error('No active user found with email: ' . $email);

            return self::FAILURE;
        }

        $name = (string) $this->option('name');

        /** @var list<string> $abilities */
        $abilities = (array) $this->option('abilities');

        if ($abilities === []) {
            $abilities = ['*'];
        }

        $token = $user->createToken($name, $abilities);

        $this->newLine();
        $this->line('  <fg=yellow;options=bold>⚠  Store this token securely — it will NOT be shown again.</>');
        $this->newLine();
        $this->line('  Token ID : <fg=cyan>' . $token->accessToken->id . '</>');
        $this->line('  Name     : <fg=cyan>' . $token->accessToken->name . '</>');
        $this->line('  User     : <fg=cyan>' . $user->email . '</>');
        $this->newLine();
        $this->line('  <fg=green;options=bold>Plain-text token (copy now):</>');
        $this->line('  ' . $token->plainTextToken);
        $this->newLine();
        $this->line('  To revoke: <fg=yellow>php artisan api:token:revoke ' . $token->accessToken->id . '</>');
        $this->newLine();

        return self::SUCCESS;
    }
}
