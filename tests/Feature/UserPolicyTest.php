<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Enums\UserRelationsEnum;
use App\Models\User;
use App\Policies\UserPolicy;
use Database\Seeders\RolesAndPermissionsSeeder;

describe('userpolicy', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        $this->other_admin = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        $this->manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $this->manager_collaborator = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $this->manager_non_collaborator = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $this->annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        $this->other_annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        $this->manager->relatedUsers()->attach($this->annotator->id, ['relation_type' => UserRelationsEnum::ANNOTATOR_OF_MANAGER->value]);
        $this->manager->relatedUsers()->attach($this->manager_collaborator->id, ['relation_type' => UserRelationsEnum::COLLABORATOR_OF_USER->value]);
        $this->policy = new UserPolicy();
    });

    it('view', function (): void {
        // Admin can view all
        expect($this->policy->view($this->admin, $this->manager))->toBeTrue();
        expect($this->policy->view($this->admin, $this->annotator))->toBeTrue();
        expect($this->policy->view($this->admin, $this->admin))->toBeTrue();
        // Manager cannot view admins
        expect($this->policy->view($this->manager, $this->admin))->toBeFalse();
        // Manager can view annotators related to him
        expect($this->policy->view($this->manager, $this->annotator))->toBeTrue();
        // Manager can view collaborators
        expect($this->policy->view($this->manager, $this->manager_collaborator))->toBeTrue();
        // Manager canot view non-collaborators
        expect($this->policy->view($this->manager, $this->manager_non_collaborator))->toBeFalse();
        // annotator cannot view anyone except himself
        expect($this->policy->view($this->annotator, $this->annotator))->toBeTrue();
        expect($this->policy->view($this->annotator, $this->other_annotator))->toBeFalse();
        expect($this->policy->view($this->annotator, $this->manager))->toBeFalse();
        expect($this->policy->view($this->annotator, $this->admin))->toBeFalse();

    });

    it('create', function (): void {
        // Admin can create all
        expect($this->policy->create($this->admin, RolesEnum::ADMIN->value))->toBeTrue();
        expect($this->policy->create($this->admin, RolesEnum::ANNOTATION_MANAGER->value))->toBeTrue();
        expect($this->policy->create($this->admin, RolesEnum::ANNOTATOR->value))->toBeTrue();
        // Manager cannot create admins
        expect($this->policy->create($this->manager, RolesEnum::ADMIN->value))->toBeFalse();
        // Manager can create annotators and managers
        expect($this->policy->create($this->manager, RolesEnum::ANNOTATION_MANAGER->value))->toBeTrue();
        expect($this->policy->create($this->manager, RolesEnum::ANNOTATOR->value))->toBeTrue();
        // annotator cannot create
        expect($this->policy->create($this->annotator, RolesEnum::ADMIN->value))->toBeFalse();
        expect($this->policy->create($this->annotator, RolesEnum::ANNOTATION_MANAGER->value))->toBeFalse();
        expect($this->policy->create($this->annotator, RolesEnum::ANNOTATOR->value))->toBeFalse();

    });

    it('update', function (): void {
        // Admin can update all
        expect($this->policy->update($this->admin, $this->manager))->toBeTrue();
        expect($this->policy->update($this->admin, $this->annotator))->toBeTrue();
        expect($this->policy->update($this->admin, $this->admin))->toBeTrue();
        // Manager can update self and annotators related to him
        expect($this->policy->update($this->manager, $this->manager))->toBeTrue();
        expect($this->policy->update($this->manager, $this->annotator))->toBeTrue();
        // Manager cannot annotators not related to him
        expect($this->policy->update($this->manager, $this->other_annotator))->toBeFalse();
        // Manager cannot update other managers or admins
        expect($this->policy->update($this->manager, $this->manager_collaborator))->toBeFalse();
        expect($this->policy->update($this->manager, $this->manager_non_collaborator))->toBeFalse();
        expect($this->policy->update($this->manager, $this->admin))->toBeFalse();
        // annotator can update only self
        expect($this->policy->view($this->annotator, $this->annotator))->toBeTrue();
        expect($this->policy->view($this->annotator, $this->other_annotator))->toBeFalse();
        expect($this->policy->view($this->annotator, $this->manager))->toBeFalse();
        expect($this->policy->view($this->annotator, $this->admin))->toBeFalse();
    });

    it('delete', function (): void {
        // Admin can delete all except self
        expect($this->policy->delete($this->admin, $this->manager))->toBeTrue();
        expect($this->policy->delete($this->admin, $this->annotator))->toBeTrue();
        expect($this->policy->delete($this->admin, $this->other_admin))->toBeTrue();
        expect($this->policy->delete($this->admin, $this->admin))->toBeFalse();
        // Manager cannot delete
        expect($this->policy->delete($this->manager, $this->manager))->toBeFalse();
        expect($this->policy->delete($this->manager, $this->manager_collaborator))->toBeFalse();
        expect($this->policy->delete($this->manager, $this->manager_non_collaborator))->toBeFalse();
        expect($this->policy->delete($this->manager, $this->annotator))->toBeFalse();
        expect($this->policy->delete($this->manager, $this->other_annotator))->toBeFalse();
        expect($this->policy->delete($this->manager, $this->admin))->toBeFalse();
        // Annotator cannot delete
        expect($this->policy->delete($this->annotator, $this->manager))->toBeFalse();
        expect($this->policy->delete($this->annotator, $this->annotator))->toBeFalse();
        expect($this->policy->delete($this->annotator, $this->other_annotator))->toBeFalse();
        expect($this->policy->delete($this->annotator, $this->admin))->toBeFalse();
    });

    it('restore', function (): void {
        // Admin can restore all
        expect($this->policy->restore($this->admin, $this->manager))->toBeTrue();
        expect($this->policy->restore($this->admin, $this->annotator))->toBeTrue();
        expect($this->policy->restore($this->admin, $this->other_admin))->toBeTrue();
        // Manager cannot restore
        expect($this->policy->restore($this->manager, $this->manager))->toBeFalse();
        expect($this->policy->restore($this->manager, $this->manager_collaborator))->toBeFalse();
        expect($this->policy->restore($this->manager, $this->manager_non_collaborator))->toBeFalse();
        expect($this->policy->restore($this->manager, $this->annotator))->toBeFalse();
        expect($this->policy->restore($this->manager, $this->other_annotator))->toBeFalse();
        expect($this->policy->restore($this->manager, $this->admin))->toBeFalse();
        // Annotator cannot restore
        expect($this->policy->restore($this->annotator, $this->manager))->toBeFalse();
        expect($this->policy->restore($this->annotator, $this->annotator))->toBeFalse();
        expect($this->policy->restore($this->annotator, $this->other_annotator))->toBeFalse();
        expect($this->policy->restore($this->annotator, $this->admin))->toBeFalse();
    });
});
