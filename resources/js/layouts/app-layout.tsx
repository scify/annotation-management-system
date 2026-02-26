import { Toaster } from '@/components/ui/sonner';
import { useFlashMessages } from '@/hooks/use-flash-messages';
import { type BreadcrumbItem } from '@/types';
import { type ReactNode } from 'react';
import AppLayoutTemplate from '@/layouts/app/app-header-layout';

interface AppLayoutProps {
	children: ReactNode;
	breadcrumbs?: BreadcrumbItem[];
}

// AppLayout delegates to the header-based layout now that the sidebar has been removed.

const AppLayout = ({ children, breadcrumbs, ...props }: AppLayoutProps) => {
	useFlashMessages();

	return (
		<AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
			{children}
			<Toaster />
		</AppLayoutTemplate>
	);
};

AppLayout.displayName = 'AppLayout';

export default AppLayout;
