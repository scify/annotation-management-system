<?php

declare(strict_types=1);

return [
    'welcome' => [
        'subject' => 'Welcome to :app',
        'greeting' => 'Hello :name,',
        'intro' => 'Your account on :app has been created.',
        'created_by' => 'Your account was created by :creator.',
        'role' => 'You have been assigned the role: :role. ',
        'outro' => 'Welcome aboard! We are glad to have you on the platform.',
        'cta' => 'Sign in to :app',
        'creator_fallback' => 'An administrator',
    ],
    'co_manager_invitation' => [
        'subject' => 'You have been invited to co-manage a project on :app',
        'greeting' => 'Hello :name,',
        'intro' => ':inviter has invited you to co-manage the project ":project".',
        'outro' => 'Sign in to review the invitation and accept or decline it.',
        'cta' => 'Sign in to :app',
    ],
];
