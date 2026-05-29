<?php

declare(strict_types=1);

use App\Enums\ProjectStatusEnum;
use App\Enums\RolesEnum;
use App\Models\Project;
use App\Models\SubProject;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

describe('SubProject dropdown actions', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('pending subproject shows Set as In Progress and Delete', function (): void {
        $project = Project::factory()->create();
        SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::PENDING,
        ]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.show', $project));

        $page->click('[aria-label="Subproject actions"]');

        $page->wait(0.1)
            ->assertSee('Set as In Progress')
            ->assertSee('Delete')
            ->assertDontSee('Set as Completed')
            ->assertNoJavascriptErrors();
    });

    it('in-progress subproject shows Set as Completed', function (): void {
        $project = Project::factory()->create();
        SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::IN_PROGRESS,
        ]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.show', $project));

        $page->click('[aria-label="Subproject actions"]');

        $page->wait(0.1)
            ->assertSee('Set as Completed')
            ->assertDontSee('Set as In Progress')
            ->assertDontSee('Delete')
            ->assertNoJavascriptErrors();
    });

    it('completed subproject shows only View/Edit and Test', function (): void {
        $project = Project::factory()->create();
        SubProject::factory()->create([
            'project_id' => $project->id,
            'status' => ProjectStatusEnum::COMPLETED,
        ]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('projects.show', $project));

        $page->click('[aria-label="Subproject actions"]');

        $page->wait(0.1)
            ->assertDontSee('Set as Completed')
            ->assertDontSee('Set as In Progress')
            ->assertDontSee('Delete')
            ->assertNoJavascriptErrors();
    });
});
