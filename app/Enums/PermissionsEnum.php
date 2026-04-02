<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionsEnum: string {
    case CREATE_ANNOTATORS = 'create_annotators';
    case MANAGE_ANNOTATORS = 'manage_annotators';
    case CREATE_MANAGERS = 'create_managers';
    case MANAGE_MANAGERS = 'manage_managers';
    case CREATE_PROJECTS = 'create_projects';
    case MANAGE_PROJECTS = 'manage_projects';
    case CONNECT_MANAGERS_TO_PROJECTS = 'connect_managers_to_projects';
    case CONNECT_ANNOTATORS_TO_PROJECTS = 'connect_annotators_to_projects';
    case CONNECT_ANNOTATORS_TO_MANAGERS = 'connect_annotators_to_managers';
    case CREATE_ADMINS = 'create_admins';
    case MANAGE_ADMINS = 'manage_admins';
    case CONNECT_MANAGERS_TO_TASKS = 'connect_managers_to_tasks';

    public function label(): string {
        return match ($this) {
            self::CREATE_ANNOTATORS => 'create_annotators',
            self::MANAGE_ANNOTATORS => 'manage_annotators',
            self::CREATE_MANAGERS => 'create_managers',
            self::MANAGE_MANAGERS => 'manage_managers',
            self::CREATE_PROJECTS => 'create_projects',
            self::MANAGE_PROJECTS => 'manage_projects',
            self::CONNECT_MANAGERS_TO_PROJECTS => 'connect_managers_to_projects',
            self::CONNECT_ANNOTATORS_TO_PROJECTS => 'connect_annotators_to_projects',
            self::CONNECT_ANNOTATORS_TO_MANAGERS => 'connect_annotators_to_managers',
            self::CREATE_ADMINS => 'create_admins',
            self::MANAGE_ADMINS => 'manage_admins',
            self::CONNECT_MANAGERS_TO_TASKS => 'connect_managers_to_tasks',
        };
    }
}
