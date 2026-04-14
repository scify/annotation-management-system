import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Mail } from 'lucide-react';

export interface UserTableCellProps {
	initials: string;
	username: string;
	/** Shown as a secondary line below the username when provided */
	email?: string;
	/** Called when the mail icon button is clicked. Omit to render the button without an action. */
	onMessage?: () => void;
}

export function UserTableCell({ initials, username, email, onMessage }: UserTableCellProps) {
	return (
		<div className="flex items-center gap-3">
			<Avatar className="size-[29px] shrink-0">
				<AvatarFallback className="bg-brand-blue-300 rounded-full text-sm font-semibold text-white">
					{initials}
				</AvatarFallback>
			</Avatar>
			<div className="flex min-w-0 flex-col">
				<span className="text-base font-medium text-slate-800">{username}</span>
				{email && <span className="text-sm text-slate-400">{email}</span>}
			</div>
			<Button
				variant="ghost"
				size="icon"
				className="bg-brand-blue-50 text-brand-blue-700 hover:bg-brand-blue-100 hover:text-brand-blue-700 size-11 shrink-0 rounded-lg"
				aria-label={`Send message to ${username}`}
				onClick={onMessage}
			>
				<Mail className="size-6" aria-hidden="true" />
			</Button>
		</div>
	);
}
