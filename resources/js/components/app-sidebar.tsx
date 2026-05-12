import AppLogoIconMinimal from '@/components/app-logo-icon-minimal';
import { isNavItemActive, useNavItems } from '@/hooks/use-nav-items';
import { cn } from '@/lib/utils';
import { Link, usePage } from '@inertiajs/react';
import { PanelLeftClose, PanelLeftOpen } from 'lucide-react';
import type { SharedData } from '@/types';

export interface AppSidebarProps {
    isCollapsed: boolean;
    onToggle: () => void;
}

export function AppSidebar({ isCollapsed, onToggle }: AppSidebarProps) {
    const page = usePage<SharedData>();
    const navItems = useNavItems();

    return (
        <aside
            aria-label="Main navigation"
            className={cn(
                'from-brand-blue-700 to-brand-blue-950 sticky top-0 hidden h-screen shrink-0 flex-col overflow-hidden rounded-tr-[20px] rounded-br-[20px] bg-gradient-to-t transition-[width] duration-300 ease-in-out motion-reduce:transition-none lg:flex',
                isCollapsed ? 'w-[52px]' : 'w-[152px]'
            )}
        >
            <div className="flex flex-1 flex-col">
                {/* Logo */}
                <div
                    className={cn(
                        'flex items-center py-5',
                        isCollapsed ? 'justify-center px-2' : 'px-4'
                    )}
                >
                    <Link href="/dashboard" prefetch aria-label="Home">
                        <AppLogoIconMinimal className="h-9 w-auto" />
                    </Link>
                </div>

                {/* Navigation items */}
                <nav className="flex flex-1 flex-col gap-1 px-2 py-2">
                    {navItems.map((item) => {
                        const active = isNavItemActive(item.href, page.url);
                        const itemClass = cn(
                            'flex items-center rounded-lg px-1 py-2 text-sm font-medium text-white transition-colors mb-2',
                            isCollapsed ? 'justify-center' : 'gap-1.5',
                            active ? 'bg-slate-800' : 'hover:bg-white/10',
                            item.placeholder && 'cursor-not-allowed opacity-60'
                        );

                        const content = (
                            <>
                                <item.icon className="size-[18px] shrink-0" aria-hidden="true" />
                                <span className={cn('truncate', isCollapsed && 'sr-only')}>
                                    {item.title}
                                </span>
                            </>
                        );

                        if (item.placeholder) {
                            return (
                                <span
                                    key={item.title}
                                    className={itemClass}
                                    aria-disabled="true"
                                    title={isCollapsed ? item.title : undefined}
                                >
                                    {content}
                                </span>
                            );
                        }

                        return (
                            <Link
                                key={item.title}
                                href={item.href}
                                prefetch
                                className={itemClass}
                                aria-current={active ? 'page' : undefined}
                                title={isCollapsed ? item.title : undefined}
                            >
                                {content}
                            </Link>
                        );
                    })}
                </nav>

                {/* Collapse button */}
                <div
                    className={cn('flex px-2 pb-3', isCollapsed ? 'justify-start' : 'justify-end')}
                >
                    <button
                        type="button"
                        onClick={onToggle}
                        className="bg-brand-blue-800 hover:bg-brand-blue-600 flex size-[30px] cursor-pointer items-center justify-center rounded-lg text-white transition-colors focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
                        aria-label={isCollapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                    >
                        {isCollapsed ? (
                            <PanelLeftOpen className="h-4 w-4" aria-hidden="true" />
                        ) : (
                            <PanelLeftClose className="h-4 w-4" aria-hidden="true" />
                        )}
                    </button>
                </div>
            </div>
        </aside>
    );
}
