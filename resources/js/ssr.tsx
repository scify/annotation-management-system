import { createInertiaApp } from '@inertiajs/react';
import createServer from '@inertiajs/react/server';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { RouterProvider } from 'react-aria-components';
import ReactDOMServer from 'react-dom/server';
import { useRoute } from 'ziggy-js';
import { type SharedData } from './types';

const appName = import.meta.env.VITE_APP_NAME ?? 'Laravel';

// useRoute is a plain factory function (not a React hook) — it has no React internals.
// Aliased here so react-hooks/rules-of-hooks doesn't false-positive when it's called
// from Inertia's setup() callback, which is not a component or hook.
const createBoundRoute = useRoute;

createServer((page) =>
	createInertiaApp({
		page,
		render: ReactDOMServer.renderToString,
		title: (title) => `${title} - ${appName}`,
		resolve: (name) =>
			resolvePageComponent(`./pages/${name}.tsx`, import.meta.glob('./pages/**/*.tsx')),
		setup: ({ App, props }) => {
			// page.props is SharedData at runtime. Inertia types it as Record<string,unknown>
			// so a double cast via unknown is required — this is TypeScript's own recommended
			// pattern for non-overlapping structural casts.
			const { ziggy } = page.props as unknown as SharedData;

			// Destructure location (string in SharedData) before spreading so we can pass
			// a URL object as required by Ziggy's Config.location type.
			const { location: locationString, ...ziggyConfig } = ziggy;

			// createBoundRoute returns a fully-typed typeof route bound to the given config,
			// which is assigned to global.route for use by components during SSR rendering.
			global.route = createBoundRoute({ ...ziggyConfig, location: new URL(locationString) });

			// RouterProvider navigate is a no-op during SSR; client-side routing handles
			// actual navigation after hydration.
			return (
				<RouterProvider navigate={() => {}}>
					<App {...props} />
				</RouterProvider>
			);
		},
	})
);
