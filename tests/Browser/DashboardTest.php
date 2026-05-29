<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

describe('Dashboard', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('shows the dashboard to an authenticated admin', function (): void {
        $admin = User::factory()->create([
            'password' => Hash::make('password'),
        ])->assignRole(RolesEnum::ADMIN);

        loginViaForm($admin->username)
            ->assertRoute('dashboard')
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors();
    });

    it('shows the dashboard to an authenticated annotation manager', function (): void {
        $manager = User::factory()->create([
            'password' => Hash::make('password'),
        ])->assignRole(RolesEnum::ANNOTATION_MANAGER);

        loginViaForm($manager->username)
            ->assertRoute('dashboard')
            ->assertSee('Dashboard')
            ->assertNoJavascriptErrors();
    });

    it('redirects unauthenticated users to the login page', function (): void {
        visit('/dashboard')
            ->assertRoute('login');
    });
});
