<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

/**
 * Logs in via the login form and returns the resulting page.
 *
 * The Altcha captcha is disabled server-side in APP_ENV=testing (only 'required' applies).
 * We bypass the frontend JavaScript guard by dispatching a synthetic statechange event.
 */
function loginViaForm(string $email, string $password = 'password'): mixed {
    $page = visit('/login')
        ->type('email', $email)
        ->type('password', $password);

    // script() returns the JS evaluation result, not $this — call it separately.
    // wait(0.1) lets React flush the captcha state update before pressing.
    $page->script("
        const widget = document.getElementById('altcha-widget');
        if (widget) {
            widget.dispatchEvent(new CustomEvent('statechange', {
                detail: { payload: 'test-token', state: 'verified' },
                bubbles: true,
            }));
        }
    ");

    return $page->wait(0.1)->press('Log in');
}

function seedRolesAndPermissions(): void {
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
}

describe('Dashboard', function (): void {
    beforeEach(fn () => seedRolesAndPermissions());

    it('shows the dashboard to an authenticated admin', function (): void {
        $admin = User::factory()->create([
            'password' => Hash::make('password'),
        ])->assignRole(RolesEnum::ADMIN);

        loginViaForm($admin->email)
            ->assertRoute('dashboard')
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors();
    });

    it('shows the dashboard to an authenticated annotation manager', function (): void {
        $manager = User::factory()->create([
            'password' => Hash::make('password'),
        ])->assignRole(RolesEnum::ANNOTATION_MANAGER);

        loginViaForm($manager->email)
            ->assertRoute('dashboard')
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors();
    });

    it('redirects unauthenticated users to the login page', function (): void {
        visit('/dashboard')
            ->assertRoute('login');
    });
});
