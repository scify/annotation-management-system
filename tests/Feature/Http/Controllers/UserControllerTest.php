<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'name' => 'Admin User',
        ])->assignRole(RolesEnum::ADMIN->value)->load('roles');

        $this->annotationManager = User::factory()->create([
            'email' => 'annotation_manager@example.com',
            'name' => 'Annotation Manager',
        ])->assignRole(RolesEnum::ANNOTATION_MANAGER->value)->load('roles');

        $this->annotator = User::factory()->create([
            'email' => 'annotator@example.com',
            'name' => 'Annotator',
        ])->assignRole(RolesEnum::ANNOTATOR->value)->load('roles');
    });

    it('shows users list to admins and annotation managers', function (): void {
        // Admin can view users
        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('users/index')->has('users')->has('abilities'));

        // Annotation manager can view users
        $this->actingAs($this->annotationManager)
            ->get(route('users.index'))
            ->assertOk();

        // Annotator cannot view users
        $this->actingAs($this->annotator)
            ->get(route('users.index'))
            ->assertForbidden();
    });

    it('returns correct per-user abilities based on policy', function (): void {
        // Admin can update/delete any user
        $this->actingAs($this->admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where(sprintf('abilities.%s.update', $this->annotationManager->id), true)
                ->where(sprintf('abilities.%s.delete', $this->annotationManager->id), true)
                ->where(sprintf('abilities.%s.update', $this->annotator->id), true)
                ->where(sprintf('abilities.%s.delete', $this->annotator->id), true)
            );

        // Annotation manager cannot update or delete admins
        $this->actingAs($this->annotationManager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where(sprintf('abilities.%s.update', $this->admin->id), false)
                ->where(sprintf('abilities.%s.delete', $this->admin->id), false)
                ->where(sprintf('abilities.%s.update', $this->annotator->id), true)
                ->where(sprintf('abilities.%s.delete', $this->annotator->id), true)
            );
    });

    it('shows create form to admins and annotation managers', function (): void {
        // Admin can view create form
        $this->actingAs($this->admin)
            ->get(route('users.create'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('users/create')->has('roles'));

        // Annotation manager can view create form
        $this->actingAs($this->annotationManager)
            ->get(route('users.create'))
            ->assertOk();

        // Annotator cannot view create form
        $this->actingAs($this->annotator)
            ->get(route('users.create'))
            ->assertForbidden();
    });

    it('creates a new user', function (): void {
        // Arrange
        $this->actingAs($this->admin)->get(route('users.create'));

        // Act
        $response = $this->post(route('users.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => RolesEnum::ANNOTATOR->value,
            '_token' => session('_token'),
        ]);

        // Assert
        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'test@example.com')->first();

        expect($user)
            ->name->toBe('Test User')
            ->and($user->hasRole(RolesEnum::ANNOTATOR->value))->toBeTrue();
    });

    it('prevents annotation managers from modifying admins', function (): void {
        // Arrange
        $adminToModify = User::factory()->create()->syncRoles([RolesEnum::ADMIN->value]);

        $this->actingAs($this->annotationManager)->get(route('users.edit', $adminToModify));

        // Act
        $response = $this->put(route('users.update', $adminToModify), [
            'name' => 'Updated Name',
            'email' => $adminToModify->email,
            'role' => RolesEnum::ADMIN->value,
            '_token' => session('_token'),
        ]);

        // Assert
        $response->assertForbidden();

        expect(User::query()->find($adminToModify->id))
            ->name->not->toBe('Updated Name');
    });

    it('soft deletes a user', function (): void {
        // Arrange
        $user = User::factory()->create();

        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->delete(route('users.destroy', $user), [
            '_token' => session('_token'),
        ]);

        // Assert
        $response->assertRedirect();

        $this->assertSoftDeleted('users', ['id' => $user->id]);

        expect(User::withTrashed()->find($user->id))->not->toBeNull()
            ->and(User::query()->find($user->id))->toBeNull();
    });
});
