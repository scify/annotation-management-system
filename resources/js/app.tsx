import '../scss/app.scss';
import { router } from '@inertiajs/react';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { RouterProvider } from 'react-aria-components';
import { createRoot } from 'react-dom/client';
import { initializeTheme } from './hooks/use-appearance';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

createInertiaApp({
	title: (title) => `${title} - ${appName}`,
	resolve: (name) =>
		resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
	setup({ el, App, props }) {
		const root = createRoot(el);

		root.render(
			<RouterProvider navigate={(to) => router.visit(to)}>
				<App {...props} />
			</RouterProvider>
		);
		delete el.dataset.page;
	},
	progress: {
		color: '#4B5563',
	},
}).catch((err) => console.error(err));

// This will set light / dark mode on load...
initializeTheme();
