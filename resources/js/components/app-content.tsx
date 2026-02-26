import * as React from 'react';

// AppContent — simplified after sidebar removal.
// SidebarInset has been removed; this renders a plain <main> wrapper.

export function AppContent({ children, ...props }: Readonly<React.ComponentProps<'main'>>) {
	return (
		<main
			className="mx-auto flex h-full w-full max-w-7xl flex-1 flex-col gap-4 rounded-xl"
			{...props}
		>
			{children}
		</main>
	);
}
