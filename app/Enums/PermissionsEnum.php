<?php

declare(strict_types=1);

namespace App\Enums;

enum PermissionsEnum: string {
    case CREATE_ANNOTATORS = 'create annotators';
    case MANAGE_ANNOTATORS = 'manage annotators';
    case CREATE_PROJECT_MANAGERS = 'create project managers';
    case MANAGE_PROJECT_MANAGERS = 'manage project managers';
    case CREATE_PROJECTS = 'create projects';
    case MANAGE_PROJECTS = 'manage projects';
    case CONNECT_PROJECT_MANAGERS_TO_PROJECTS = 'connect project managers to projects';
    case ASSIGN_ANNOTATORS_TO_PROJECTS = 'assign annotators to projects';
    case CONNECT_ANNOTATORS_TO_PROJECT_MANAGERS = 'connect annotators to project managers';
    case CREATE_ADMINS = 'create admins';
    case MANAGE_ADMINS = 'manage admins';

    public function label(): string {
        return match ($this) {
            self::CREATE_ANNOTATORS => 'create annotators',
            self::MANAGE_ANNOTATORS => 'manage annotators',
            self::CREATE_PROJECT_MANAGERS => 'create project managers',
            self::MANAGE_PROJECT_MANAGERS => 'manage project managers',
            self::CREATE_PROJECTS => 'create projects',
            self::MANAGE_PROJECTS => 'manage projects',
            self::CONNECT_PROJECT_MANAGERS_TO_PROJECTS => 'connect project managers to projects',
            self::ASSIGN_ANNOTATORS_TO_PROJECTS => 'assign annotators to projects',
            self::CONNECT_ANNOTATORS_TO_PROJECT_MANAGERS => 'connect annotators to project managers',
            self::CREATE_ADMINS => 'create admins',
            self::MANAGE_ADMINS => 'manage admins',
        };
    }
}
