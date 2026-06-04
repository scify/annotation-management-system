<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\AnnotatorOfManager;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

describe('Monitor', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('redirects unauthenticated users to the login page', function (): void {
        visit(route('monitor.annotator-progress'))
            ->assertRoute('login');
    });

    describe('as admin', function (): void {
        it('shows the monitor page with tabs and the annotators toggle', function (): void {
            $admin = User::factory()->create(['password' => Hash::make('password')])
                ->assignRole(RolesEnum::ADMIN);

            loginViaForm($admin->username)
                ->navigate(route('monitor.annotator-progress'))
                ->assertSee('Monitor')
                ->assertSee('Annotator Progress')
                ->assertSee('Annotator History')
                ->assertSee('Showing all annotators')
                ->assertSee('No annotators.')
                ->assertNoJavascriptErrors();
        });

        it('shows all annotators to an admin', function (): void {
            $admin = User::factory()->create(['password' => Hash::make('password')])
                ->assignRole(RolesEnum::ADMIN);

            $annotator = User::factory()->create()
                ->assignRole(RolesEnum::ANNOTATOR);

            loginViaForm($admin->username)
                ->navigate(route('monitor.annotator-progress'))
                ->assertSee($annotator->username)
                ->assertNoJavascriptErrors();
        });
    });

    describe('as annotation manager', function (): void {
        it('shows the monitor page without tabs or toggle', function (): void {
            $manager = User::factory()->create(['password' => Hash::make('password')])
                ->assignRole(RolesEnum::ANNOTATION_MANAGER);

            loginViaForm($manager->username)
                ->navigate(route('monitor.annotator-progress'))
                ->assertSee('Monitor')
                ->assertDontSee('Annotator History')
                ->assertDontSee('Showing all annotators')
                ->assertSee('No annotators.')
                ->assertNoJavascriptErrors();
        });

        it('shows linked annotators to an annotation manager', function (): void {
            $manager = User::factory()->create(['password' => Hash::make('password')])
                ->assignRole(RolesEnum::ANNOTATION_MANAGER);

            $annotator = User::factory()->create()
                ->assignRole(RolesEnum::ANNOTATOR);

            AnnotatorOfManager::query()->create([
                'manager_id' => $manager->id,
                'annotator_id' => $annotator->id,
            ]);

            loginViaForm($manager->username)
                ->navigate(route('monitor.annotator-progress'))
                ->assertSee($annotator->username)
                ->assertNoJavascriptErrors();
        });
    });
});
