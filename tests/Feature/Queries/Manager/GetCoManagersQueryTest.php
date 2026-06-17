<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\User;
use App\Queries\Manager\GetCoManagersQuery;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('GetCoManagersQuery', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('returns active and pending admins and annotation managers', function (): void {
        // Arrange
        $activeManager = User::factory()->create(['status' => StatusEnum::ACTIVE])->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $pendingAdmin = User::factory()->create(['status' => StatusEnum::PENDING])->assignRole(RolesEnum::ADMIN);

        // Act
        $coManagers = new GetCoManagersQuery()->get();

        // Assert
        expect($coManagers->pluck('id')->all())->toEqualCanonicalizing([$activeManager->id, $pendingAdmin->id]);
    });

    it('excludes inactive users', function (): void {
        // Arrange
        User::factory()->create(['status' => StatusEnum::INACTIVE])->assignRole(RolesEnum::ANNOTATION_MANAGER);

        // Act
        $coManagers = new GetCoManagersQuery()->get();

        // Assert
        expect($coManagers)->toBeEmpty();
    });

    it('excludes users without a manager or admin role', function (): void {
        // Arrange
        User::factory()->create(['status' => StatusEnum::ACTIVE])->assignRole(RolesEnum::ANNOTATOR);

        // Act
        $coManagers = new GetCoManagersQuery()->get();

        // Assert
        expect($coManagers)->toBeEmpty();
    });

    it('restricts the result to the given ids when provided', function (): void {
        // Arrange
        $wanted = User::factory()->create(['status' => StatusEnum::ACTIVE])->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $other = User::factory()->create(['status' => StatusEnum::ACTIVE])->assignRole(RolesEnum::ANNOTATION_MANAGER);

        // Act
        $coManagers = new GetCoManagersQuery()->get([$wanted->id]);

        // Assert
        expect($coManagers->pluck('id')->all())->toBe([$wanted->id])
            ->and($coManagers->pluck('id')->all())->not->toContain($other->id);
    });

    it('selects only the lightweight display columns', function (): void {
        // Arrange
        $manager = User::factory()->create(['status' => StatusEnum::ACTIVE])->assignRole(RolesEnum::ANNOTATION_MANAGER);

        // Act
        $coManager = new GetCoManagersQuery()->get()->sole();

        // Assert
        expect($coManager->getAttributes())->toHaveKeys(['id', 'username', 'name', 'status'])
            ->and($coManager->id)->toBe($manager->id);
    });
});
