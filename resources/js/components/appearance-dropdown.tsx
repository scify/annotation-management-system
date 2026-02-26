import { Button } from '@/components/ui/button';
import {
	DropdownMenu,
	DropdownMenuContent,
	DropdownMenuItem,
	DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useAppearance } from '@/hooks/use-appearance';
import { Monitor, Moon, Sun } from 'lucide-react';
import { HTMLAttributes } from 'react';

export default function AppearanceToggleDropdown({
	className = '',
	...props
}: HTMLAttributes<HTMLDivElement>) {
	const { appearance, updateAppearance } = useAppearance();

	const getCurrentIcon = () => {
		switch (appearance) {
			case 'dark':
				return <Moon className="h-5 w-5" />;
			case 'light':
				return <Sun className="h-5 w-5" />;
			default:
				return <Monitor className="h-5 w-5" />;
		}
	};

	return (
		<div className={className} {...props}>
			<DropdownMenu>
				{/* asChild passes the Button through directly so MenuTrigger sees
                    one AriaButton in the tree (Button renders AriaButton internally). */}
				<DropdownMenuTrigger asChild>
					<Button variant="ghost" size="icon" className="h-9 w-9 rounded-md">
						{getCurrentIcon()}
						<span className="sr-only">Toggle theme</span>
					</Button>
				</DropdownMenuTrigger>
				<DropdownMenuContent align="end">
					<DropdownMenuItem onAction={() => updateAppearance('light')}>
						<span className="flex items-center gap-2">
							<Sun className="h-5 w-5" />
							Light
						</span>
					</DropdownMenuItem>
					<DropdownMenuItem onAction={() => updateAppearance('dark')}>
						<span className="flex items-center gap-2">
							<Moon className="h-5 w-5" />
							Dark
						</span>
					</DropdownMenuItem>
					<DropdownMenuItem onAction={() => updateAppearance('system')}>
						<span className="flex items-center gap-2">
							<Monitor className="h-5 w-5" />
							System
						</span>
					</DropdownMenuItem>
				</DropdownMenuContent>
			</DropdownMenu>
		</div>
	);
}
