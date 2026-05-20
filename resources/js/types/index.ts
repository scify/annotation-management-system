import type { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';
import type { MouseEventHandler } from 'react';

type PermissionAction = 'view' | 'create' | 'update' | 'delete' | 'restore' | 'connect' | 'manage';
type PermissionResource =
    | 'admins'
    | 'annotators'
    | 'managers'
    | 'projects'
    | 'annotators_to_managers'
    | 'annotators_to_projects'
    | 'managers_to_projects'
    | 'managers_to_tasks';
type Permission = `${PermissionAction}_${PermissionResource}`;

export interface User {
    id: number;
    name: string;
    email: string;
    role: RolesEnum | null;
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    avatar: string | null;
}

export type AuthUser = User & {
    can: Record<Permission, boolean>;
};

export interface Auth {
    user: AuthUser | null;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href?: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    children?: NavItem[];
    defaultOpen?: boolean;
    onClick?: MouseEventHandler;
}

export interface AppData {
    name: string;
    version: string;
    env: string;
    locale: string;
}

export type TranslationRecord = Record<string, string | Record<string, string>>;

export interface TranslationMap {
    auth: TranslationRecord;
    'client-applications': TranslationRecord;
    common: TranslationRecord;
    dashboard: TranslationRecord;
    monitor: TranslationRecord;
    navbar: TranslationRecord;
    pagination: TranslationRecord;
    passwords: TranslationRecord;
    projects: TranslationRecord;
    roles: TranslationRecord;
    settings: TranslationRecord;
    'sub-projects': TranslationRecord;
    users: TranslationRecord;
    validation: TranslationRecord;
}

export interface SharedData {
    app: AppData;
    quote: { message: string; author: string };
    flash: {
        success: string | null;
        error: string | null;
        warning: string | null;
        info: string | null;
    };
    auth: Auth;
    ziggy: Config & { location: string };
    translations: TranslationMap;
    errors?: Record<string, string>;
    [key: string]: unknown;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: {
        user: AuthUser;
    };
    ziggy: Config & { location: string };
};

export type ProjectStatus = 'pending' | 'in_progress' | 'completed';

export interface Project {
    id: number;
    name: string;
    owner_user_id: number;
    annotation_task_id: number;
    status: ProjectStatus;
    dataset_id: number;
    annotation_task_title: string | null;
    dataset_name: string | null;
    subprojects_count: number;
    annotators_count: number;
    notifications_count: number;
    owner_name: string | null;
    co_managers: Array<{ id: number; username: string }> | null;
    project_progress: number;
    started_at: string | null;
    completed_at: string | null;
    scheduled_at: string | null;
    deadline_at: string | null;
    is_delayed_to_start: boolean;
    is_delayed_to_end: boolean;
}

export interface PlatformStats {
    all_projects: number;
    all_annotators: number;
    all_managers: number;
    all_admins: number;
}

export interface Annotator {
    id: number;
    name: string;
    annotator_progress: number;
    active_projects_count: number;
    active_subprojects_count: number;
    workload: number;
}

export enum RolesEnum {
    ADMIN = 'admin',
    ANNOTATION_MANAGER = 'annotation-manager',
    ANNOTATOR = 'annotator',
}
