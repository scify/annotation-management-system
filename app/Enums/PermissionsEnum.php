<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionsEnum: string {
    case CREATE_ANNOTATORS = 'create annotators';
    case MANAGE_ANNOTATORS = 'manage annotators';
    case CREATE_MANAGERS = 'create managers';
    case MANAGE_MANAGERS = 'manage managers';
    case CREATE_PROJECTS = 'create projects';
    case MANAGE_PROJECTS = 'manage projects';
    case CONNECT_MANAGERS_TO_PROJECTS = 'connect managers to projects';
    case CONNECT_ANNOTATORS_TO_PROJECTS = 'connect annotators to projects';
    case CONNECT_ANNOTATORS_TO_MANAGERS = 'connect annotators to managers';
    case CREATE_ADMINS = 'create admins';
    case MANAGE_ADMINS = 'manage admins';
    case CONNECT_MANAGERS_TO_TASKS = 'connect managers to tasks';

    public function label(): string {
        return match ($this) {
            self::CREATE_ANNOTATORS => 'create annotators',
            self::MANAGE_ANNOTATORS => 'manage annotators',
            self::CREATE_MANAGERS => 'create managers',
            self::MANAGE_MANAGERS => 'manage managers',
            self::CREATE_PROJECTS => 'create projects',
            self::MANAGE_PROJECTS => 'manage projects',
            self::CONNECT_MANAGERS_TO_PROJECTS => 'connect managers to projects',
            self::CONNECT_ANNOTATORS_TO_PROJECTS => 'connect annotators to projects',
            self::CONNECT_ANNOTATORS_TO_MANAGERS => 'connect annotators to managers',
            self::CREATE_ADMINS => 'create admins',
            self::MANAGE_ADMINS => 'manage admins',
            self::CONNECT_MANAGERS_TO_TASKS => 'connect managers to tasks',
        };
    }
}
