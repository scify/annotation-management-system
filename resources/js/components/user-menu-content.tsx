import {
	DropdownMenuGroup,
	DropdownMenuItem,
	DropdownMenuLabel,
	DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { UserInfo } from '@/components/user-info';
import { useMobileNavigation } from '@/hooks/use-mobile-navigation';
import { type User } from '@/types';
import { router } from '@inertiajs/react';
import { LogOut, Settings } from 'lucide-react';
import { useTranslations } from '@/hooks/use-translations';

interface UserMenuContentProps {
	user: User;
}

export function UserMenuContent({ user }: Readonly<UserMenuContentProps>) {
	const cleanup = useMobileNavigation();
	const { t } = useTranslations();

	return (
		<>
			<DropdownMenuLabel className="p-0 font-normal">
				<div className="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
					<UserInfo user={user} showEmail={true} />
				</div>
			</DropdownMenuLabel>
			<DropdownMenuSeparator />
			<DropdownMenuGroup>
				<DropdownMenuItem
					onAction={() => {
						cleanup();
						router.visit(route('profile.edit'));
					}}
				>
					<Settings />
					{t('common.settings')}
				</DropdownMenuItem>
			</DropdownMenuGroup>
			<DropdownMenuSeparator />
			<DropdownMenuItem
				onAction={() => {
					cleanup();
					router.post(route('logout'));
				}}
			>
				<LogOut />
				{t('common.log_out')}
			</DropdownMenuItem>
		</>
	);
}
