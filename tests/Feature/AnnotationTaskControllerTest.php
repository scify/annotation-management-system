<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('AnnotationTaskController', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('annotation-tasks.show', ['subProject' => 1]))
            ->assertRedirect('/login');
    });

    it('renders the annotation-task page with the subproject id and requested mode', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation-tasks.show', ['subProject' => 7, 'mode' => 'flexible']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('annotation-task/index')
                ->where('subProjectId', 7)
                ->where('mode', 'flexible'));
    });

    it('defaults to strict mode when no mode is provided', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation-tasks.show', ['subProject' => 7]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('mode', 'strict'));
    });

    it('falls back to strict mode when an invalid mode is provided', function (): void {
        // Arrange
        $user = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR->value);

        // Act & Assert
        $this->actingAs($user)
            ->get(route('annotation-tasks.show', ['subProject' => 7, 'mode' => 'bogus']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('mode', 'strict'));
    });
});
