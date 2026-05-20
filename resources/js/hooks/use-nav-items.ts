import { useTranslations } from '@/hooks/use-translations';
import { type SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { Activity, BellRing, Captions, FolderDot, LayoutDashboard, Users } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

export interface SidebarNavItem {
    title: string;
    href: string;
    icon: LucideIcon;
    placeholder?: boolean;
}

export function isNavItemActive(href: string, currentUrl: string): boolean {
    if (href === '#') return false;
    const path = href.startsWith('http') ? new URL(href).pathname : href;
    if (path === '/dashboard') return currentUrl === path;
    return currentUrl === path || currentUrl.startsWith(path + '/');
}

export function useNavItems(): SidebarNavItem[] {
    const { auth } = usePage<SharedData>().props;
    console.log('Auth data in useNavItems:', auth);
    const { t } = useTranslations();

    return [
        { title: t('navbar.dashboard'), icon: LayoutDashboard, href: '/dashboard' },
        { title: t('navbar.projects'), icon: FolderDot, href: route('projects.index') },
        { title: t('navbar.monitor'), icon: Activity, href: route('monitor.index') },
        ...(auth?.user?.can.manage_admins || auth?.user?.can.manage_annotators
            ? [{ title: t('navbar.users'), icon: Users, href: route('users.index') }]
            : []),
        { title: t('navbar.notifications'), icon: BellRing, href: '#', placeholder: true },
        { title: t('navbar.audit_log'), icon: Captions, href: '#', placeholder: true },
    ];
}
