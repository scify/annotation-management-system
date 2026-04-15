import '../scss/app.scss';
import { router } from '@inertiajs/react';
import { createInertiaApp } from '@inertiajs/react';
import { RouterProvider } from 'react-aria-components';
import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

createInertiaApp({
	title: (title) => `${title} - ${appName}`,
	progress: {
		color: '#4B5563',
	},
	withApp(app) {
		return <RouterProvider navigate={(to) => router.visit(to)}>{app}</RouterProvider>;
	},
}).catch((err) => console.error(err));

// This will set light / dark mode on load...
initializeTheme();
