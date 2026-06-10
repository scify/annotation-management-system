<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Enums\StatusEnum;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * Create a project with an owner and one co-manager, both annotation managers.
 *
 * @param  array<string, mixed>  $coManagerPivot  overrides for the co-manager's project_managers row
 *
 * @return array{0: Project, 1: User, 2: User} [project, owner, coManager]
 */
function createCoManagedProject(array $coManagerPivot = []): array {
    $owner = User::factory()->create([
        'password' => Hash::make('password'),
        'status' => StatusEnum::ACTIVE,
    ]);
    $owner->assignRole(RolesEnum::ANNOTATION_MANAGER);

    $coManager = User::factory()->create([
        'password' => Hash::make('password'),
        'status' => StatusEnum::ACTIVE,
    ]);
    $coManager->assignRole(RolesEnum::ANNOTATION_MANAGER);

    $project = Project::factory()->create(['owner_user_id' => $owner->id]);

    ProjectManager::factory()->create([
        'project_id' => $project->id,
        'user_id' => $owner->id,
    ]);
    ProjectManager::factory()->create([
        'project_id' => $project->id,
        'user_id' => $coManager->id,
        ...$coManagerPivot,
    ]);

    return [$project, $owner, $coManager];
}

describe('Co-Managers tab', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('shows Transfer and Remove to the owner for an accepted co-manager', function (): void {
        [$project, $owner] = createCoManagedProject();

        $page = loginViaForm($owner->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Transfer')
            ->assertSee('Remove')
            ->assertDontSee('Request to leave')
            ->assertDontSee('Ownership Request')
            ->assertNoJavascriptErrors();
    });

    it('shows Request to leave to a co-manager and hides owner-only actions', function (): void {
        [$project, , $coManager] = createCoManagedProject();

        $page = loginViaForm($coManager->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Request to leave')
            ->assertDontSee('Remove')
            ->assertDontSee('Transfer')
            ->assertNoJavascriptErrors();
    });

    it('shows a Pending badge and disables Transfer for an invited co-manager', function (): void {
        [$project, $owner] = createCoManagedProject(['accepted' => false]);

        $page = loginViaForm($owner->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Pending')
            ->assertSee('Remove');

        $transferDisabled = $page->script(
            "Array.from(document.querySelectorAll('button')).find((b) => b.textContent.trim() === 'Transfer')?.disabled ?? null"
        );
        expect($transferDisabled)->toBeTrue();

        $page->assertNoJavascriptErrors();
    });

    it('shows the Leave Request button to the owner when a co-manager requested to leave', function (): void {
        [$project, $owner] = createCoManagedProject(['request_to_leave' => true]);

        $page = loginViaForm($owner->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Leave Request')
            ->assertDontSee('Requested')
            ->assertNoJavascriptErrors();
    });

    it('shows the Requested state with Undo to the co-manager who requested to leave', function (): void {
        [$project, , $coManager] = createCoManagedProject(['request_to_leave' => true]);

        $page = loginViaForm($coManager->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Requested')
            ->assertSee('Undo')
            ->assertNoJavascriptErrors();
    });

    it('shows the Ownership Request button to the proposed co-manager', function (): void {
        [$project, , $coManager] = createCoManagedProject(['proposed_to_become_owner' => true]);

        $page = loginViaForm($coManager->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Ownership Request')
            ->assertNoJavascriptErrors();
    });

    it('shows the Requested state to the owner for a proposed co-manager', function (): void {
        [$project, $owner] = createCoManagedProject(['proposed_to_become_owner' => true]);

        $page = loginViaForm($owner->username)
            ->navigate(route('projects.show', $project));

        $page->click('#tab-managers');

        $page->wait(0.1)
            ->assertSee('Requested')
            ->assertSee('Undo')
            ->assertDontSee('Ownership Request')
            ->assertNoJavascriptErrors();
    });
});
