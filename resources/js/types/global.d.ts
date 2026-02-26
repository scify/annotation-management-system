import type { route as routeFn } from 'ziggy-js';

declare global {
    // var (not const) so the SSR entry point can assign global.route
    var route: typeof routeFn;
}
