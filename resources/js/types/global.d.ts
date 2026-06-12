import type { route as routeFn } from 'ziggy-js';

declare global {
    // var (not const) so the SSR entry point can assign global.route
    var route: typeof routeFn;

    interface Window {
        // Exposed by the scify/laravel-cookie-guard package (resources/js/scripts.js).
        // Optional because it's only defined once the package's script has loaded client-side.
        toggleCookieBanner?: () => void;
    }
}

// The altcha package's React JSX types (altcha/types/react) don't extend HTMLAttributes,
// so standard props like `id`, `hidelogo`, and `hidefooter` are missing. We declare the
// element ourselves with the full attribute set instead of loading the incomplete types.
declare module 'react/jsx-runtime' {
    namespace JSX {
        interface IntrinsicElements {
            'altcha-widget': import('react').DetailedHTMLProps<
                import('react').HTMLAttributes<HTMLElement>,
                HTMLElement
            > & {
                auto?: string;
                challenge?: string;
                configuration?: string;
                display?: string;
                hidefooter?: boolean;
                hidelogo?: boolean;
                language?: string;
                name?: string;
                type?: string;
                workers?: number;
            };
        }
    }
}
