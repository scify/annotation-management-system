import { useAuth } from '@/hooks/use-auth';
import { useTranslations } from '@/hooks/use-translations';
import {
    Activity,
    BellRing,
    Captions,
    ChartColumnBig,
    Cookie,
    FolderDot,
    LayoutDashboard,
    Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';

export interface SidebarNavItem {
    title: string;
    href: string;
    icon: LucideIcon;
    placeholder?: boolean;
    /** When set, the item renders as an action button instead of a navigation link. */
    onClick?: () => void;
}

export function isNavItemActive(href: string, currentUrl: string): boolean {
    if (href === '#') return false;
    const path = href.startsWith('http') ? new URL(href).pathname : href;
    if (path === '/dashboard') return currentUrl === path;
    return currentUrl === path || currentUrl.startsWith(path + '/');
}

export function useNavItems(): SidebarNavItem[] {
    const { isAdmin, isAnnotationManager, isAnnotator } = useAuth();
    const { t } = useTranslations();

    if (isAnnotator()) {
        return [
            { title: t('navbar.dashboard'), icon: LayoutDashboard, href: '/dashboard' },
            {
                title: t('navbar.notifications'),
                icon: BellRing,
                href: route('notifications.index'),
            },
            // "My Reports" has no route yet — render as a disabled placeholder.
            { title: t('navbar.my_reports'), icon: ChartColumnBig, href: '#', placeholder: true },
        ];
    }

    return [
        { title: t('navbar.dashboard'), icon: LayoutDashboard, href: '/dashboard' },
        { title: t('navbar.projects'), icon: FolderDot, href: route('projects.index') },
        ...(isAdmin() || isAnnotationManager()
            ? [{ title: t('navbar.monitor'), icon: Activity, href: route('monitor.index') }]
            : []),
        ...(isAdmin() || isAnnotationManager()
            ? [{ title: t('navbar.users'), icon: Users, href: route('users.index') }]
            : []),
        { title: t('navbar.notifications'), icon: BellRing, href: route('notifications.index') },
        { title: t('navbar.audit_log'), icon: Captions, href: '#', placeholder: true },
        {
            title: t('navbar.cookies_settings'),
            icon: Cookie,
            href: '#',
            onClick: () => window.toggleCookieBanner?.(),
        },
    ];
}
