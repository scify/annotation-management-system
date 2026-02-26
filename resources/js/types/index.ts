import type { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';
import type { MouseEventHandler } from 'react';

type PermissionAction = 'view' | 'create' | 'update' | 'delete';
type PermissionResource = 'users' /* | 'posts' | 'comments' etc */;
type Permission = `${PermissionAction}_${PermissionResource}`;

export interface User {
	id: number;
	name: string;
	email: string;
	role: string;
	created_at: string | null;
	updated_at: string | null;
	deleted_at: string | null;
	email_verified_at: string | null;
	avatar: string | null;
	can: Record<Permission, boolean>;
}

export interface Auth {
	user: User | null;
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
	[key: string]: unknown;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
	auth: {
		user: User;
	};
	ziggy: Config & { location: string };
};

export enum RolesEnum {
	ADMIN = 'admin',
	ANNOTATION_MANAGER = 'annotation-manager',
	ANNOTATOR = 'annotator',
}
