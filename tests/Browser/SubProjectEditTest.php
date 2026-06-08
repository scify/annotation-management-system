<?php

declare(strict_types=1);

use App\Enums\ConfidenceEnum;
use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Annotation;
use App\Models\AnnotationAssignment;
use App\Models\DatasetInstance;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

describe('SubProject edit page', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    // ── Access control ────────────────────────────────────────────────────────

    it('redirects unauthenticated users to login', function (): void {
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create(['project_id' => $project->id]);

        visit(route('projects.subprojects.edit', [$project->id, $subProject->id]))
            ->assertRoute('login');
    });

    // ── Page structure ────────────────────────────────────────────────────────

    it('shows the three tabs and defaults to the Annotations tab', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        $page->assertSee('Annotations')
            ->assertSee('Annotators')
            ->assertSee('Overview & Settings')
            ->assertNoJavascriptErrors();

        $isAnnotationsTabActive = $page->script("
            document.querySelector('[role=\"tab\"][aria-selected=\"true\"]')?.textContent?.includes('Annotations') ?? false
        ");
        expect($isAnnotationsTabActive)->toBeTrue();
    });

    // ── Tab navigation ────────────────────────────────────────────────────────

    it('switches to the Annotators tab and shows the assigned annotator', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        AnnotationAssignment::factory()->create([
            'user_id' => $annotator->id,
            'sub_project_id' => $subProject->id,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        $page->click('text=Annotators')
            ->wait(0.1)
            ->assertSee($annotator->username)
            ->assertNoJavascriptErrors();
    });

    it('switches to the Overview & Settings tab and shows pre-filled name', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'name' => 'Batch Zeta',
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        $page->click('text=Overview & Settings')
            ->wait(0.1)
            ->assertSee('Save changes')
            ->assertNoJavascriptErrors();

        $nameValue = $page->script("
            document.querySelector('input[aria-label=\"Subproject\\'s name\"]')?.value ?? ''
        ");
        expect($nameValue)->toBe('Batch Zeta');
    });

    // ── Annotations tab — confidence column (Bug: null confidence showed "Low") ─

    it('shows no confidence badge when confidence is null on a not_annotated entry', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
        $instance = DatasetInstance::factory()->create();
        Annotation::factory()->create([
            'annotation_assignment_id' => $assignment->id,
            'dataset_instance_id' => $instance->id,
            'annotations' => null,   // not_annotated status
            'confidence' => null,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        // Expand the instance row to reveal per-annotation entry details
        $page->click(sprintf('[aria-label="Expand instance #%s"]', $instance->id))
            ->wait(0.1)
            ->assertSee('Not Annotated')
            ->assertNoJavascriptErrors();

        // No confidence badge should appear inside expanded entry rows.
        // Selector .pt-4.pb-4 scopes to entry rows only — the instance-level aggregate
        // agreement row uses h-14 and would not be selected.
        $confidenceInEntries = $page->script("
            document.querySelectorAll('.pt-4.pb-4 .bg-rose-100, .pt-4.pb-4 .bg-yellow-50, .pt-4.pb-4 .bg-green-50').length
        ");
        expect($confidenceInEntries)->toBe(0);
    });

    it('shows the correct confidence badge for a submitted entry', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
        $instance = DatasetInstance::factory()->create();
        Annotation::factory()->create([
            'annotation_assignment_id' => $assignment->id,
            'dataset_instance_id' => $instance->id,
            'annotations' => '{"label":"positive"}',   // submitted status
            'confidence' => ConfidenceEnum::HIGH,
            'last_edited_by' => $annotator->id,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        $page->click(sprintf('[aria-label="Expand instance #%s"]', $instance->id))
            ->wait(0.1)
            ->assertSee('High')
            ->assertNoJavascriptErrors();
    });

    // ── Annotations tab — last-edited-by / timestamp columns (Bug: shown for not_annotated) ─

    it('hides last edited by and timestamp columns for not_annotated entries', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $annotator = User::factory()->create(['username' => 'assignee_zeta'])
            ->assignRole(RolesEnum::ANNOTATOR);
        // Editor is a different user — their username should not appear on the page
        $editor = User::factory()->create(['username' => 'editor_omega'])
            ->assignRole(RolesEnum::ANNOTATOR);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
        $instance = DatasetInstance::factory()->create();
        Annotation::factory()->create([
            'annotation_assignment_id' => $assignment->id,
            'dataset_instance_id' => $instance->id,
            'annotations' => null,              // not_annotated
            'last_edited_by' => $editor->id,    // editor exists but row is not_annotated
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        // Expand to reveal the annotation entry — assignee appears, editor does not
        $page->click(sprintf('[aria-label="Expand instance #%s"]', $instance->id))
            ->wait(0.1)
            ->assertSee('assignee_zeta')
            ->assertDontSee('editor_omega')
            ->assertNoJavascriptErrors();
    });

    it('shows last edited by and timestamp for submitted entries', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);
        $annotator = User::factory()->create(['username' => 'submitter_kappa'])
            ->assignRole(RolesEnum::ANNOTATOR);
        $project = Project::factory()->create();
        $subProject = SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        $assignment = AnnotationAssignment::factory()->create([
            'user_id' => $annotator->id,
            'sub_project_id' => $subProject->id,
        ]);
        $instance = DatasetInstance::factory()->create();
        Annotation::factory()->create([
            'annotation_assignment_id' => $assignment->id,
            'dataset_instance_id' => $instance->id,
            'annotations' => '{"label":"negative"}',  // submitted
            'confidence' => ConfidenceEnum::MEDIUM,
            'last_edited_by' => $annotator->id,
        ]);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.subprojects.edit', [$project->id, $subProject->id]));

        $page->click(sprintf('[aria-label="Expand instance #%s"]', $instance->id))
            ->wait(0.1)
            ->assertSee('Medium')
            ->assertNoJavascriptErrors();

        // Username appears in both "Assigned to" AND "Last Edited By" columns for submitted entries
        $occurrences = $page->script("
            document.body.innerText.split('submitter_kappa').length - 1
        ");
        expect($occurrences)->toBeGreaterThanOrEqual(2);
    });
});
