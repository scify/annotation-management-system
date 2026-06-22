<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\RolesEnum;
use App\Models\User;
use App\Notifications\UserWelcomeNotification;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

// example usage: ddev artisan mail:test-welcome you@example.com --name="Jane Doe"

#[Description('Smoke-test the queue and email pipeline by sending UserWelcomeNotification to a throwaway (non-persisted) user')]
#[Signature('mail:test-welcome
                            {email : Recipient email address for the test welcome notification}
                            {--name=Test User : Display name used in the email greeting}
                            {--role=annotation-manager : Role label shown in the email (admin|annotation-manager|annotator)}')]
class TestWelcomeEmailCommand extends Command {
    public function handle(): int {
        $email = (string) $this->argument('email');
        $name = (string) $this->option('name');

        $validator = Validator::make(['email' => $email], ['email' => ['required', 'email']]);

        if ($validator->fails()) {
            $this->error('Invalid email address: ' . $email);

            return self::FAILURE;
        }

        $role = RolesEnum::tryFrom((string) $this->option('role'));

        if ($role === null) {
            $this->error('Invalid role. Allowed: admin, annotation-manager, annotator.');

            return self::FAILURE;
        }

        $locale = app()->getLocale();

        // Dispatch a closure (captures only scalars) rather than notifying an unsaved
        // User directly: UserWelcomeNotification is ShouldQueue, and SerializesModels
        // would try to restore the notifiable by primary key on the worker — which
        // fails for a model that was never persisted. Building the user worker-side
        // and calling notifyNow() sidesteps that while still exercising the real queue.
        dispatch(function () use ($email, $name, $role, $locale): void {
            new User(['name' => $name, 'email' => $email])
                ->notifyNow(new UserWelcomeNotification(null, $role)->locale($locale));
        });

        $queueConnection = config()->string('queue.default');
        $mailMailer = config()->string('mail.default');

        $this->newLine();
        $this->line('  <fg=green;options=bold>Welcome notification dispatched.</>');
        $this->line('  Recipient  : <fg=cyan>' . $email . '</>');
        $this->line('  Role       : <fg=cyan>' . $role->value . '</>');
        $this->line('  Queue      : <fg=cyan>' . $queueConnection . '</>');
        $this->line('  Mailer     : <fg=cyan>' . $mailMailer . '</>');
        $this->newLine();

        if ($queueConnection !== 'sync') {
            $this->line('  <fg=yellow>A queue worker must be running to deliver this:</> php artisan queue:work');
            $this->line('  <fg=yellow>Delivered mail appears in Mailpit/MailHog (SMTP 127.0.0.1:1025).</>');
            $this->newLine();
        }

        return self::SUCCESS;
    }
}
