<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Faker\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UserController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AnnotatorPasswordPolicySeeder::class);
        $this->faker = Factory::create();

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
        // Annotation manager cannot update or delete annotators that are not related to him
        $this->actingAs($this->annotationManager)
            ->get(route('users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where(sprintf('abilities.%s.update', $this->admin->id), false)
                ->where(sprintf('abilities.%s.delete', $this->admin->id), false)
                ->where(sprintf('abilities.%s.update', $this->annotator->id), false)
                ->where(sprintf('abilities.%s.delete', $this->annotator->id), false)
            );
    });

    it('shows create form to admins', function (): void {
        $url = route('users.create', ['type' => RolesEnum::ANNOTATOR->value]);

        // Admin can view create form
        $this->actingAs($this->admin)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('users/create')->has('annotator_data'));

        // Annotation manager can view create form for an annotator-type target user
        $this->actingAs($this->annotationManager)
            ->get($url)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('users/create')->has('annotator_data'));

        // Annotator cannot view create form
        $this->actingAs($this->annotator)
            ->get($url)
            ->assertForbidden();
    });

    it('redirects to users index when create is accessed with an invalid type', function (): void {
        $this->actingAs($this->admin)
            ->get(route('users.create', ['type' => 'invalid-role']))
            ->assertRedirect(route('users.index'));
    });

    it('creates a new annotator', function (): void {
        // Arrange
        $this->actingAs($this->admin)->get(route('users.create', ['type' => RolesEnum::ANNOTATOR->value]));
        $username = $this->faker->unique()->userName();

        // Act
        $response = $this->post(route('users.store'), [
            'type' => RolesEnum::ANNOTATOR->value,
            'name' => 'Test Annotator',
            'username' => $username,
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'manager_ids' => [$this->annotationManager->id],
            '_token' => session('_token'),
        ]);

        // Assert
        $response->assertRedirect(route('users.index'));

        $user = User::query()->where('username', $username)->first();

        expect($user)
            ->not->toBeNull()
            ->name->toBe('Test Annotator')
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

    it('soft deletes an active or inactive user', function (): void {
        // Arrange
        $user = User::factory()->create(['status' => StatusEnum::ACTIVE]);

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

    it('hard deletes a pending user', function (): void {
        // Arrange
        $user = User::factory()->create(['status' => StatusEnum::PENDING]);

        $this->actingAs($this->admin)->get(route('users.index'));

        // Act
        $response = $this->delete(route('users.destroy', $user), [
            '_token' => session('_token'),
        ]);

        // Assert
        $response->assertRedirect();

        expect(User::withTrashed()->find($user->id))->toBeNull();
    });
});
