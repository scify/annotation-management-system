<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * Logs in via the login form and returns the resulting page.
 *
 * The Altcha captcha is disabled server-side in APP_ENV=testing (only 'required' applies).
 * We bypass the frontend JavaScript guard by dispatching a synthetic statechange event.
 */
function loginViaForm(string $email, string $password = 'password'): mixed {
    $page = visit('/login');

    // Fail early if JS errors prevent the page from loading correctly.
    $page->assertNoJavascriptErrors();

    $page->type('email', $email)
        ->type('password', $password);

    // Debug: confirm typing worked — if this fails, the input isn't receiving text.
    $emailValue = $page->script("document.getElementById('email')?.value ?? 'NOT FOUND'");
    expect($emailValue)->toBe($email, 'email input was not filled — check if React mounted and controlled inputs are wired');

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

describe('Dashboard', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

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
