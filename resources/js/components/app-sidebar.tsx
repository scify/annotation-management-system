import AppLogoIconMinimal from '@/components/app-logo-icon-minimal';
import { cn } from '@/lib/utils';
import { type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
	BellRing,
	ClipboardList,
	FolderOpen,
	LayoutGrid,
	PanelLeftClose,
	ScrollText,
	Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useEffect, useRef } from 'react';
import { useTranslations } from '@/hooks/use-translations';

export interface AppSidebarProps {
	isCollapsed: boolean;
	onToggle: () => void;
}

interface SidebarItem {
	title: string;
	href: string;
	icon: LucideIcon;
	placeholder?: boolean;
}

function isActive(href: string, currentUrl: string): boolean {
	if (href === '#') return false;
	if (href === '/dashboard') return currentUrl === href;
	return currentUrl.startsWith(href);
}

export function AppSidebar({ isCollapsed, onToggle }: AppSidebarProps) {
	const page = usePage<SharedData>();
	const { auth } = page.props;
	const { t } = useTranslations();
	const innerRef = useRef<HTMLDivElement>(null);

	// Manage inert attribute to remove collapsed sidebar from keyboard/AT access
	useEffect(() => {
		const el = innerRef.current;
		if (!el) return;
		if (isCollapsed) {
			el.setAttribute('inert', '');
		} else {
			el.removeAttribute('inert');
		}
	}, [isCollapsed]);

	const navItems: SidebarItem[] = [
		{ title: 'Dashboard', icon: LayoutGrid, href: '/dashboard' },
		{ title: 'Projects', icon: FolderOpen, href: '#', placeholder: true },
		{ title: 'Assignments', icon: ClipboardList, href: '#', placeholder: true },
		...(auth?.user?.can.view_users
			? [{ title: t('navbar.users'), icon: Users, href: route('users.index') }]
			: []),
		{ title: 'Notifications', icon: BellRing, href: '#', placeholder: true },
		{ title: 'Audit Log', icon: ScrollText, href: '#', placeholder: true },
	];

	return (
		<aside
			aria-label="Main navigation"
			aria-hidden={isCollapsed || undefined}
			className={cn(
				'sticky top-0 hidden h-screen shrink-0 flex-col overflow-hidden rounded-tr-[20px] rounded-br-[20px] bg-gradient-to-t from-[#4d6fd1] to-[#27396b] transition-[width] duration-300 ease-in-out motion-reduce:transition-none lg:flex',
				isCollapsed ? 'w-0' : 'w-[152px]'
			)}
		>
			{/* Inner wrapper keeps min-width so content doesn't squish during animation */}
			<div ref={innerRef} className="flex min-w-[152px] flex-1 flex-col">
				{/* Logo */}
				<div className="flex items-center px-4 py-5">
					<Link href="/dashboard" prefetch aria-label="Home">
						<AppLogoIconMinimal className="h-9 w-auto" />
					</Link>
				</div>

				{/* Navigation items */}
				<nav className="flex flex-1 flex-col gap-1 px-2 py-2">
					{navItems.map((item) => {
						const active = isActive(item.href, page.url);
						const itemClass = cn(
							'flex items-center gap-1.5 rounded-lg px-1 py-2 text-sm font-medium text-white transition-colors',
							active ? 'bg-[#1e293b]' : 'hover:bg-white/10',
							item.placeholder && 'cursor-not-allowed opacity-60'
						);

						if (item.placeholder) {
							return (
								<span key={item.title} className={itemClass} aria-disabled="true">
									<item.icon
										className="size-[18px] shrink-0"
										aria-hidden="true"
									/>
									{item.title}
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
							>
								<item.icon className="size-[18px] shrink-0" aria-hidden="true" />
								{item.title}
							</Link>
						);
					})}
				</nav>

				{/* Collapse button */}
				<div className="flex justify-end px-2 pb-3">
					<button
						type="button"
						onClick={onToggle}
						className="flex size-[30px] items-center justify-center rounded-lg bg-[#3d5bb3] text-white transition-colors hover:bg-[#4d6fbe] focus-visible:outline focus-visible:outline-2 focus-visible:outline-white"
						aria-label="Collapse sidebar"
					>
						<PanelLeftClose className="h-4 w-4" aria-hidden="true" />
					</button>
				</div>
			</div>
		</aside>
	);
}
