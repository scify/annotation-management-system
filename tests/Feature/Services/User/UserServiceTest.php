<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Exceptions\UserCreationException;
use App\Models\User;
use App\Services\User\UserService;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('UserService', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->service = resolve(UserService::class);
    });

    // --- create() routing & guard ---

    it('routes to createAdmin and persists an admin with the ADMIN role', function (): void {
        // Arrange / Act
        $user = $this->service->create([
            'role' => RolesEnum::ADMIN->value,
            'name' => 'New Admin',
            'username' => 'new_admin',
            'email' => 'new_admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
        ]);

        // Assert
        expect($user->hasRole(RolesEnum::ADMIN->value))->toBeTrue();
        $this->assertDatabaseHas('users', ['username' => 'new_admin', 'email' => 'new_admin@example.com']);
    });

    it('throws InvalidArgumentException when create() receives an unknown role', function (): void {
        expect(fn (): User => $this->service->create(['role' => 'nonexistent-role']))
            ->toThrow(InvalidArgumentException::class);
    });

    // --- update() routing & guard ---

    it('throws InvalidArgumentException when update() receives an unknown role', function (): void {
        $user = User::factory()->create();

        expect(fn (): User => $this->service->update($user, ['role' => 'nonexistent-role']))
            ->toThrow(InvalidArgumentException::class);
    });

    // --- createAnnotator() guards ---

    it('throws duplicateName when creating an annotator with an existing name', function (): void {
        User::factory()->create(['name' => 'Taken Name']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATOR->value,
            'name' => 'Taken Name',
            'username' => 'unique_annotator',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'manager_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws duplicateUsername when creating an annotator with an existing username', function (): void {
        User::factory()->create(['username' => 'taken_username']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATOR->value,
            'name' => 'Unique Annotator',
            'username' => 'taken_username',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'manager_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws passwordMismatch when creating an annotator with mismatched passwords', function (): void {
        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATOR->value,
            'name' => 'Mismatch Annotator',
            'username' => 'mismatch_annotator',
            'password' => 'password123',
            'password_confirmation' => 'different456',
            'manager_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    // --- createAdmin() guards & happy path ---

    it('throws duplicateName when creating an admin with an existing name', function (): void {
        User::factory()->create(['name' => 'Admin Taken Name']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ADMIN->value,
            'name' => 'Admin Taken Name',
            'username' => 'unique_admin',
            'email' => 'unique_admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws duplicateUsername when creating an admin with an existing username', function (): void {
        User::factory()->create(['username' => 'admin_taken_username']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ADMIN->value,
            'name' => 'Unique Admin',
            'username' => 'admin_taken_username',
            'email' => 'unique_admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws duplicateEmail when creating an admin with an existing email', function (): void {
        User::factory()->create(['email' => 'admin_taken@example.com']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ADMIN->value,
            'name' => 'Unique Admin',
            'username' => 'unique_admin',
            'email' => 'admin_taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws passwordMismatch when creating an admin with mismatched passwords', function (): void {
        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ADMIN->value,
            'name' => 'Mismatch Admin',
            'username' => 'mismatch_admin',
            'email' => 'mismatch_admin@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different456',
            'project_ids' => [],
            'annotator_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    // --- createManager() guards ---

    it('throws duplicateName when creating a manager with an existing name', function (): void {
        User::factory()->create(['name' => 'Manager Taken Name']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'Manager Taken Name',
            'username' => 'unique_manager',
            'email' => 'unique_manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
            'annotation_task_ids' => [],
            'dataset_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws duplicateUsername when creating a manager with an existing username', function (): void {
        User::factory()->create(['username' => 'manager_taken_username']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'Unique Manager',
            'username' => 'manager_taken_username',
            'email' => 'unique_manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
            'annotation_task_ids' => [],
            'dataset_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws duplicateEmail when creating a manager with an existing email', function (): void {
        User::factory()->create(['email' => 'manager_taken@example.com']);

        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'Unique Manager',
            'username' => 'unique_manager',
            'email' => 'manager_taken@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'project_ids' => [],
            'annotator_ids' => [],
            'annotation_task_ids' => [],
            'dataset_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    it('throws passwordMismatch when creating a manager with mismatched passwords', function (): void {
        expect(fn (): User => $this->service->create([
            'role' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'Mismatch Manager',
            'username' => 'mismatch_manager',
            'email' => 'mismatch_manager@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different456',
            'project_ids' => [],
            'annotator_ids' => [],
            'annotation_task_ids' => [],
            'dataset_ids' => [],
        ]))->toThrow(UserCreationException::class);
    });

    // --- updateAnnotator() ---

    it('updates an annotator without changing the password when none is provided', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $originalPassword = $user->password;

        $updated = $this->service->update($user, [
            'role' => RolesEnum::ANNOTATOR->value,
            'name' => 'Renamed Annotator',
            'username' => 'renamed_annotator',
            'manager_ids' => [],
        ]);

        expect($updated->name)->toBe('Renamed Annotator')
            ->and($updated->username)->toBe('renamed_annotator')
            ->and($updated->password)->toBe($originalPassword)
            ->and($updated->hasRole(RolesEnum::ANNOTATOR->value))->toBeTrue();
    });

    it('updates an annotator password when one is provided', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);
        $originalPassword = $user->password;

        $updated = $this->service->update($user, [
            'role' => RolesEnum::ANNOTATOR->value,
            'name' => 'Repassworded Annotator',
            'username' => 'repassworded_annotator',
            'password' => 'brand-new-password',
            'manager_ids' => [],
        ]);

        expect($updated->password)->not->toBe($originalPassword);
    });

    // --- updateAdmin() ---

    it('updates an admin and syncs the ADMIN role', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        $updated = $this->service->update($user, [
            'role' => RolesEnum::ADMIN->value,
            'name' => 'Promoted Admin',
            'username' => 'promoted_admin',
            'email' => 'promoted_admin@example.com',
            'password' => 'brand-new-password',
            'project_ids' => [],
            'annotator_ids' => [],
        ]);

        expect($updated->name)->toBe('Promoted Admin')
            ->and($updated->email)->toBe('promoted_admin@example.com')
            ->and($updated->hasRole(RolesEnum::ADMIN->value))->toBeTrue()
            ->and($updated->hasRole(RolesEnum::ANNOTATOR->value))->toBeFalse();
    });

    // --- updateManager() optional-password branch ---

    it('updates a manager password when one is provided', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value);
        $originalPassword = $user->password;

        $updated = $this->service->update($user, [
            'role' => RolesEnum::ANNOTATION_MANAGER->value,
            'name' => 'Repassworded Manager',
            'username' => 'repassworded_manager',
            'email' => 'repassworded_manager@example.com',
            'password' => 'brand-new-password',
            'project_ids' => [],
            'annotator_ids' => [],
            'annotation_task_ids' => [],
            'dataset_ids' => [],
        ]);

        expect($updated->password)->not->toBe($originalPassword)
            ->and($updated->name)->toBe('Repassworded Manager');
    });

    // --- findByEmail() ---

    it('finds a user by email', function (): void {
        $user = User::factory()->create(['email' => 'findme@example.com']);

        expect($this->service->findByEmail('findme@example.com')?->id)->toBe($user->id);
    });

    it('returns null when no user matches the email', function (): void {
        expect($this->service->findByEmail('missing@example.com'))->toBeNull();
    });

    // --- getRolesForForm() ---

    it('returns every role for an authenticated admin', function (): void {
        $admin = User::factory()->create()->assignRole(RolesEnum::ADMIN->value);
        $this->actingAs($admin);

        $roles = $this->service->getRolesForForm();

        expect($roles->pluck('name')->all())->toBe([
            RolesEnum::ADMIN->value,
            RolesEnum::ANNOTATION_MANAGER->value,
            RolesEnum::ANNOTATOR->value,
        ])->and($roles->first()['label'])->toBe('roles.' . RolesEnum::ADMIN->value);
    });

    it('returns only manager and annotator roles for a non-admin', function (): void {
        $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value);
        $this->actingAs($manager);

        $roles = $this->service->getRolesForForm();

        expect($roles->pluck('name')->all())->toBe([
            RolesEnum::ANNOTATION_MANAGER->value,
            RolesEnum::ANNOTATOR->value,
        ]);
    });
});
