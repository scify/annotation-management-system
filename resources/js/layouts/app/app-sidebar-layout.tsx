import { AppContent } from '@/components/app-content';
import AppLogoIcon from '@/components/app-logo-icon';
import { AppSidebar } from '@/components/app-sidebar';
import { Breadcrumbs } from '@/components/breadcrumbs';
import { LocaleToggle } from '@/components/locale-toggle';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Sheet, SheetContent, SheetHeader, SheetTitle, SheetTrigger } from '@/components/ui/sheet';
import { UserMenuContent } from '@/components/user-menu-content';
import { useInitials } from '@/hooks/use-initials';
import { useTranslations } from '@/hooks/use-translations';
import { cn } from '@/lib/utils';
import { type BreadcrumbItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import {
	BellRing,
	Briefcase,
	FolderDot,
	LayoutDashboard,
	Menu,
	Captions,
	Users,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { useState } from 'react';
import type { PropsWithChildren } from 'react';

interface MobileNavItem {
	title: string;
	href: string;
	icon: LucideIcon;
	placeholder?: boolean;
}

function isMobileItemActive(href: string, currentUrl: string): boolean {
	if (href === '#') return false;
	if (href === '/dashboard') return currentUrl === href;
	return currentUrl.startsWith(href);
}

export default function AppSidebarLayout({
	children,
	breadcrumbs = [],
	notificationCount = 0,
}: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[]; notificationCount?: number }>) {
	const page = usePage<SharedData>();
	const { auth } = page.props;
	const getInitials = useInitials();
	const { t } = useTranslations();
	const [isCollapsed, setIsCollapsed] = useState(false);

	const toggleSidebar = () => setIsCollapsed((prev) => !prev);

	const mobileNavItems: MobileNavItem[] = [
		{ title: t('navbar.dashboard'), icon: LayoutDashboard, href: '/dashboard' },
		{ title: t('navbar.projects'), icon: FolderDot, href: route('projects.index') },
		{ title: t('navbar.assignments'), icon: Briefcase, href: '#', placeholder: true },
		...(auth?.user?.can.view_users
			? [{ title: t('navbar.users'), icon: Users, href: route('users.index') }]
			: []),
		{ title: t('navbar.notifications'), icon: BellRing, href: '#', placeholder: true },
		{ title: t('navbar.audit_log'), icon: Captions, href: '#', placeholder: true },
	];

	return (
		<div className="flex min-h-screen w-full">
			<AppSidebar isCollapsed={isCollapsed} onToggle={toggleSidebar} />

			<div className="flex min-w-0 flex-1 flex-col bg-slate-50">
				{/* Mobile top bar — hidden on desktop */}
				<div className="border-sidebar-border/80 flex h-14 items-center border-b px-4 lg:hidden">
					<Sheet>
						<SheetTrigger asChild>
							<Button
								variant="ghost"
								size="icon"
								className="mr-2 h-[34px] w-[34px]"
								aria-label={t('common.navigation_menu_label')}
							>
								<Menu className="h-5 w-5" />
							</Button>
						</SheetTrigger>
						<SheetContent
							side="left"
							className="from-brand-blue-700 to-brand-blue-950 flex h-full w-[152px] flex-col rounded-tr-[20px] rounded-br-[20px] bg-gradient-to-t p-0"
						>
							<SheetTitle className="sr-only">
								{t('common.navigation_menu_label')}
							</SheetTitle>
							<SheetHeader className="px-4 py-5">
								<AppLogoIcon className="h-9 w-auto" />
							</SheetHeader>
							<nav
								className="flex flex-1 flex-col gap-1 px-2 py-2"
								aria-label="Mobile navigation"
							>
								{mobileNavItems.map((item) => {
									const active = isMobileItemActive(item.href, page.url);
									const itemClass = cn(
										'flex items-center gap-1.5 rounded-lg px-1 py-2 text-sm font-medium text-white transition-colors mb-2',
										active ? 'bg-slate-800' : 'hover:bg-white/10',
										item.placeholder && 'cursor-not-allowed opacity-60'
									);

									if (item.placeholder) {
										return (
											<span
												key={item.title}
												className={itemClass}
												aria-disabled="true"
											>
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
											<item.icon
												className="size-[18px] shrink-0"
												aria-hidden="true"
											/>
											{item.title}
										</Link>
									);
								})}
								<div className="mt-auto px-1 pb-2">
									<LocaleToggle className="w-full justify-start rounded-lg px-1 py-2 text-sm font-medium text-white hover:bg-white/10" />
								</div>
							</nav>
						</SheetContent>
					</Sheet>

					<Link href="/dashboard" prefetch aria-label="Home">
						<AppLogoIcon className="h-7 w-auto" />
					</Link>

					<div className="ml-auto">
						<DropdownMenu>
							<DropdownMenuTrigger asChild>
								<Button variant="ghost" className="size-10 rounded-full p-1">
									<Avatar className="size-8 overflow-hidden rounded-full">
										<AvatarImage
											src={auth?.user?.avatar ?? ''}
											alt={auth?.user?.name ?? ''}
										/>
										<AvatarFallback className="rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white">
											{getInitials(auth?.user?.name ?? '')}
										</AvatarFallback>
									</Avatar>
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent className="w-56" align="end">
								{auth?.user && <UserMenuContent user={auth.user} />}
							</DropdownMenuContent>
						</DropdownMenu>
					</div>
				</div>

				{/* Desktop top bar — user info + notifications, hidden on mobile */}
				<div className="hidden items-center justify-end px-6 py-4 lg:flex">
					<div className="flex items-center gap-1.5">
						{/* Avatar + name — opens settings/logout dropdown */}
						<DropdownMenu>
							<DropdownMenuTrigger asChild>
								<Button
									variant="ghost"
									className="hover:bg-brand-blue-50 flex h-auto items-center gap-1 rounded-lg px-2 py-1.5 text-slate-600 hover:text-slate-700"
								>
									<Avatar className="size-[29px] shrink-0">
										<AvatarImage
											src={auth?.user?.avatar ?? ''}
											alt={auth?.user?.name ?? ''}
										/>
										<AvatarFallback className="bg-brand-blue-300 rounded-full text-sm font-semibold text-white">
											{getInitials(auth?.user?.name ?? '')}
										</AvatarFallback>
									</Avatar>
									<span className="text-base font-medium">
										@{auth?.user?.name}
									</span>
								</Button>
							</DropdownMenuTrigger>
							<DropdownMenuContent className="w-56" align="end">
								{auth?.user && <UserMenuContent user={auth.user} />}
							</DropdownMenuContent>
						</DropdownMenu>

						<LocaleToggle className="bg-brand-blue-50 hover:bg-brand-blue-75 focus-visible:outline-brand-blue-700 rounded-lg px-2.5 py-[10px] text-sm font-semibold text-slate-600 transition-colors focus-visible:outline focus-visible:outline-2" />

						{/* Notifications button */}
						<div className="relative">
							<button
								type="button"
								className="bg-brand-blue-50 hover:bg-brand-blue-75 focus-visible:outline-brand-blue-700 rounded-lg p-[10px] text-slate-600 transition-colors focus-visible:outline focus-visible:outline-2"
								aria-label={
									notificationCount > 0
										? `Notifications — ${notificationCount} unread`
										: 'Notifications'
								}
							>
								<BellRing className="size-6" />
							</button>
							{notificationCount > 0 && (
								<span
									aria-hidden="true"
									className="bg-brand-red-700 absolute -top-0.5 -right-1 flex min-w-[17px] items-center justify-center rounded-full px-[4.5px] text-xs leading-[18px] font-semibold text-white"
								>
									{notificationCount}
								</span>
							)}
						</div>
					</div>
				</div>

				{/* Breadcrumbs */}
				{breadcrumbs.length > 1 && (
					<div className="border-sidebar-border/70 flex w-full border-b">
						<div className="flex h-12 w-full items-center px-6 text-neutral-500">
							<Breadcrumbs breadcrumbs={breadcrumbs} />
						</div>
					</div>
				)}

				{/* Page content */}
				<AppContent>{children}</AppContent>
			</div>
		</div>
	);
}
