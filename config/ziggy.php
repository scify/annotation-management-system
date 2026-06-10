<?php

declare(strict_types=1);

// This is a list of routes that will be available in the Ziggy router.
return [
    'only' => ['dashboard', 'users.index', 'users.create', 'users.store', 'users.show',
        'users.edit', 'users.update', 'users.destroy', 'users.restore',
        'logout', 'login', 'home', 'profile.edit', 'profile.update', 'profile.destroy',
        'password.edit', 'password.update', 'appearance', 'client-applications.index',
        'altcha-challenge', 'password.request', 'password.email',
        'password.reset', 'password.store', 'verification.notice', 'verification.verify',
        'verification.send', 'password.confirm', 'password.confirmation', 'projects.index',
        'projects.show', 'projects.subprojects.create', 'locale.update', 'projects.create',
        'projects.subprojects.edit', 'monitor.index', 'projects.store', 'monitor.annotator-progress',
        'monitor.annotator-history', 'projects.export', 'projects.subprojects.store',
        'projects.toggle-can-flag', 'settings.annotator-password-policy.update',
        'projects.annotators.add', 'projects.annotators.attach',
        'projects.subprojects.annotators.add', 'projects.subprojects.annotators.attach',
        'projects.annotators.detach', 'projects.subprojects.annotators.detach',
        'projects.subprojects.update', 'projects.change-status', 'sub-projects.change-status',
        'projects.subprojects.destroy', 'projects.destroy', 'projects.propose-ownership',
        'projects.accept-ownership', 'projects.reject-ownership',
        'users.annotators.add', 'users.annotators.connect', 'projects.cancel-ownership',
    ],
];
