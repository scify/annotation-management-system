<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

function seedRolesAndPermissions(): void {
    Artisan::call('db:seed', ['--class' => RolesAndPermissionsSeeder::class]);
}

describe('Authentication', function (): void {
    beforeEach(fn () => seedRolesAndPermissions());

    it('redirects to the dashboard after a successful login', function (): void {
        $user = User::factory()->create([
            'password' => Hash::make('password'),
        ])->assignRole(RolesEnum::ADMIN);

        $page = visit('/login');
        $page->assertNoJavascriptErrors();

        $page->type('email', $user->email)
            ->type('password', 'password');

        $emailValue = $page->script("document.getElementById('email')?.value ?? 'NOT FOUND'");
        expect($emailValue)->toBe($user->email, 'email input was not filled');

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

        $page->wait(0.1)->press('Log in')
            ->assertRoute('dashboard')
            ->assertNoJavascriptErrors();
    });

    it('stays on the login page after invalid credentials', function (): void {
        User::factory()->create([
            'email' => 'user@example.com',
            'password' => Hash::make('correct-password'),
        ])->assignRole(RolesEnum::ADMIN);

        $page = visit('/login')
            ->type('email', 'user@example.com')
            ->type('password', 'wrong-password');

        $page->script("
            const widget = document.getElementById('altcha-widget');
            if (widget) {
                widget.dispatchEvent(new CustomEvent('statechange', {
                    detail: { payload: 'test-token', state: 'verified' },
                    bubbles: true,
                }));
            }
        ");

        $page->wait(0.1)->press('Log in')
            ->assertRoute('login');
    });
});
