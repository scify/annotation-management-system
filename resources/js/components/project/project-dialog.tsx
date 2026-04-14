import {
	Dialog,
	DialogContent,
	DialogDescription,
	DialogFooter,
	DialogHeader,
	DialogTitle,
} from '@/components/ui/dialog';
import { cn } from '@/lib/utils';

export interface ProjectDialogProps {
	open: boolean;
	onClose: () => void;
	/** Lucide icon — sized and coloured by the wrapper */
	icon: React.ReactNode;
	title: string;
	description: React.ReactNode;
	/** Optional content between description and footer (warning box, textarea, etc.) */
	children?: React.ReactNode;
	/** Label for the cancel/reject button. Defaults to "Cancel" */
	cancelLabel?: string;
	/** Optional icon rendered to the left of the cancel label */
	cancelIcon?: React.ReactNode;
	actionLabel: string;
	/** Optional icon rendered to the right of the action label */
	actionIcon?: React.ReactNode;
	onAction: () => void;
	/**
	 * - `standard`    → solid brand-blue-700 (e.g. Send message)
	 * - `highlighted` → brand-blue-800 + 4px cyan-400 ring (e.g. Approve, Send Request)
	 */
	actionStyle?: 'standard' | 'highlighted';
}

export function ProjectDialog({
	open,
	onClose,
	icon,
	title,
	description,
	children,
	cancelLabel = 'Cancel',
	cancelIcon,
	actionLabel,
	actionIcon,
	onAction,
	actionStyle = 'standard',
}: ProjectDialogProps) {
	return (
		<Dialog
			open={open}
			onOpenChange={(isOpen) => {
				if (!isOpen) onClose();
			}}
		>
			<DialogContent className="flex flex-col gap-6 rounded-2xl p-6 shadow-[0px_2px_5px_3px_rgba(0,0,0,0.1)] sm:max-w-sm">
				<DialogHeader className="flex flex-col gap-4 pb-6">
					{/* Icon */}
					<div className="text-brand-blue-700 [&_svg]:size-9">{icon}</div>

					<div className="flex flex-col gap-4">
						<DialogTitle className="text-xl leading-9 font-bold text-slate-700">
							{title}
						</DialogTitle>
						<DialogDescription className="text-sm leading-5 font-medium text-slate-500">
							{description}
						</DialogDescription>
					</div>
				</DialogHeader>

				{children}

				<DialogFooter className="flex-row gap-[22px]">
					<button
						type="button"
						onClick={onClose}
						className="text-brand-blue-900 flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg bg-yellow-300 text-base font-semibold transition-colors hover:bg-yellow-400"
					>
						{cancelIcon}
						{cancelLabel}
					</button>
					<button
						type="button"
						onClick={onAction}
						className={cn(
							'flex h-10 flex-1 cursor-pointer items-center justify-center gap-2 rounded-lg text-base font-semibold text-white transition-colors',
							actionStyle === 'highlighted'
								? 'bg-brand-blue-800 hover:bg-brand-blue-900 border-4 border-cyan-400'
								: 'bg-brand-blue-700 hover:bg-brand-blue-800'
						)}
					>
						{actionLabel}
						{actionIcon}
					</button>
				</DialogFooter>
			</DialogContent>
		</Dialog>
	);
}
