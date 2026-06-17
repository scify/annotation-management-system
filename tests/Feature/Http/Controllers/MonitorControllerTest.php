<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotationAssignment;
use App\Models\AnnotationTask;
use App\Models\Dataset;
use App\Models\DatasetInstance;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

/**
 * Builds a minimal monitor graph: one user per role, plus a single annotator who is
 * connected to one IN_PROGRESS project (owned by the admin) and assigned to one of its
 * subprojects. Exactly one annotator exists, so the admin's `all_annotators` list is
 * deterministic (count 1) for the enriched assertions below.
 */
function bootMonitorFixtures(): object {
    $admin = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');
    $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER)->load('roles');
    $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR)->load('roles');

    $task = AnnotationTask::factory()->create();

    $dataset = Dataset::query()->create([
        'name' => 'Monitor Dataset',
        'description' => 'A test dataset',
        'is_available' => true,
    ]);

    foreach (range(1, 10) as $i) {
        DatasetInstance::query()->create([
            'dataset_id' => $dataset->id,
            'index' => $i,
            'content' => 'instance ' . $i,
        ]);
    }

    $project = Project::query()->create([
        'name' => 'Monitor Project',
        'owner_user_id' => $admin->id,
        'annotation_task_id' => $task->id,
        'dataset_id' => $dataset->id,
        'status' => ProjectStatusEnum::IN_PROGRESS,
        'restricted_visibility' => false,
        'is_instance_shuffled' => false,
    ]);

    $project->annotators()->syncWithoutDetaching([$annotator->id]);

    $subProject = SubProject::factory()->create([
        'project_id' => $project->id,
        'status' => ProjectStatusEnum::IN_PROGRESS,
    ]);

    AnnotationAssignment::factory()->create([
        'user_id' => $annotator->id,
        'sub_project_id' => $subProject->id,
    ]);

    return (object) ['admin' => $admin, 'manager' => $manager, 'annotator' => $annotator, 'project' => $project, 'subProject' => $subProject];
}

describe('MonitorController::index', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
    });

    it('redirects authenticated users to the annotator progress tab', function (): void {
        $user = User::factory()->create()->assignRole(RolesEnum::ADMIN)->load('roles');

        $this->actingAs($user)
            ->get(route('monitor.index'))
            ->assertRedirect(route('monitor.annotator-progress'));
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('monitor.index'))->assertRedirect(route('login'));
    });
});

describe('MonitorController::annotatorProgress', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        $this->ctx = bootMonitorFixtures();
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('monitor.annotator-progress'))->assertRedirect(route('login'));
    });

    it('renders the monitor page with the progress prop for an annotator', function (): void {
        $this->actingAs($this->ctx->annotator)
            ->get(route('monitor.annotator-progress'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_progress_tab_data'));
    });

    it('renders the monitor page with the progress prop for an annotation manager', function (): void {
        $this->actingAs($this->ctx->manager)
            ->get(route('monitor.annotator-progress'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_progress_tab_data'));
    });

    it('renders the monitor page with the progress prop for an admin', function (): void {
        $this->actingAs($this->ctx->admin)
            ->get(route('monitor.annotator-progress'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_progress_tab_data'));
    });

    it('includes the connected annotator in the admin all_annotators list', function (): void {
        $this->actingAs($this->ctx->admin)
            ->get(route('monitor.annotator-progress'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_progress_tab_data.all_annotators', 1)
                ->where('annotator_progress_tab_data.all_annotators.0.id', $this->ctx->annotator->id));
    });
});

describe('MonitorController::annotatorHistory', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        Storage::fake('local');
        $this->ctx = bootMonitorFixtures();
    });

    it('redirects guests to the login page', function (): void {
        $this->get(route('monitor.annotator-history'))->assertRedirect(route('login'));
    });

    it('renders the monitor page with the history prop for an annotator', function (): void {
        $this->actingAs($this->ctx->annotator)
            ->get(route('monitor.annotator-history'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_history_tab_data'));
    });

    it('renders the monitor page with the history prop for an annotation manager', function (): void {
        $this->actingAs($this->ctx->manager)
            ->get(route('monitor.annotator-history'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_history_tab_data'));
    });

    it('renders the monitor page with the history prop for an admin', function (): void {
        $this->actingAs($this->ctx->admin)
            ->get(route('monitor.annotator-history'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_history_tab_data'));
    });

    it('includes the connected annotator in the admin all_annotators list', function (): void {
        $this->actingAs($this->ctx->admin)
            ->get(route('monitor.annotator-history'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page): AssertableInertia => $page
                ->component('monitor/index')
                ->has('annotator_history_tab_data.all_annotators', 1)
                ->where('annotator_history_tab_data.all_annotators.0.id', $this->ctx->annotator->id));
    });
});
