import type { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';
import type { MouseEventHandler } from 'react';

export type ServerPermission =
    | 'connect_annotators_to_managers'
    | 'connect_annotators_to_projects'
    | 'connect_managers_to_projects'
    | 'connect_managers_to_tasks'
    | 'create_admins'
    | 'create_annotators'
    | 'create_managers'
    | 'create_projects'
    | 'manage_admins'
    | 'manage_annotators'
    | 'manage_managers'
    | 'manage_projects';

/**
 * User represents a data record: a user from the database as returned by the API.
 * It has no "can" attribute, because arbitrary user records don't carry the current session's permission context.
 */
export interface User {
    id: number;
    name: string;
    email: string;
    role: RolesEnum | null;
    status: 'active' | 'inactive' | 'pending';
    created_at: string | null;
    updated_at: string | null;
    deleted_at: string | null;
    avatar: string | null;
}

/**
 * AuthUser represents the authenticated principal: the currently logged-in user,
 * which the server enriches with the can permissions map in HandleInertiaRequests.
 * That can property only makes sense for the session user — it answers "what can I do", not
  "what can this person do".
 */
export type AuthUser = User & {
    can: Record<ServerPermission, boolean>;
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
        created_project_name: string | null;
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
    annotators?: number[];
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

export interface AnnotationTaskOption {
    id: number;
    title: string;
    description: string;
    short_description: string;
    guidelines_url: string | null;
    customization_options: unknown[];
    tags: Array<{ id: number; name: string }>;
    datasets: Array<{ id: number; name: string; description: string; instances_count: number }>;
}

export interface ManagerCreateData {
    my_projects: Project[];
    my_annotators: AnnotatorSelectOption[];
    annotation_tasks: AnnotationTaskOption[];
    all_projects?: Project[];
    all_annotators?: AnnotatorSelectOption[];
}

/** Annotator entry as returned in admin_data / manager_data for user creation.
 *  Stats fields are present on my_annotators but absent on all_annotators. */
export interface AnnotatorSelectOption {
    id: number;
    name: string;
    username: string;
    status: 'active' | 'inactive' | 'pending';
    total_projects?: number;
    total_subprojects?: number;
    total_annotations?: number;
    total_flags?: number;
}

/** Shape of the admin_data Inertia prop on /users/create?type=admin */
export interface AdminCreateData {
    all_projects: Project[];
    my_projects: Project[];
    all_annotators: AnnotatorSelectOption[];
    my_annotators: AnnotatorSelectOption[];
}

/** Shape of the annotator_data Inertia prop on /users/create?type=annotator */
export interface AnnotatorCreateData {
    all_managers: ManagedUser[];
}

/** A user entry as returned by UserManagementService — lighter than the full User model */
export interface ManagedUser {
    id: number;
    name: string;
    username: string;
    email?: string;
    status: 'active' | 'inactive' | 'pending';
    role: RolesEnum;
}

/** Shape of the `management` Inertia prop — fields are role-conditional */
export interface UserManagement {
    admins?: ManagedUser[];
    all_managers?: ManagedUser[];
    my_managers?: ManagedUser[];
    all_annotators?: ManagedUser[];
    my_annotators?: ManagedUser[];
}
