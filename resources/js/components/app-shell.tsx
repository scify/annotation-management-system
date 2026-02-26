// App shell — simplified after sidebar removal.
// The sidebar variant has been removed; the component now renders a plain flex column.

export function AppShell({ children }: Readonly<{ children: React.ReactNode }>) {
	return <div className="flex min-h-screen w-full flex-col">{children}</div>;
}
