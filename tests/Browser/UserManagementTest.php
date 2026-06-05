<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\AnnotatorOfManager;
use App\Models\AnnotatorOfProject;
use App\Models\Project;
use App\Models\ProjectManager;
use App\Models\User;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Hash;

/**
 * Fill the password confirmation field (2nd password input) via the native React-compatible
 * setter — both password inputs share autocomplete="new-password", so a CSS selector alone
 * cannot distinguish them.
 */
function fillPasswordConfirmation(mixed $page, string $password): void {
    $escapedPassword = json_encode($password);
    $page->script("
        const inputs = document.querySelectorAll('input[type=\"password\"]');
        const setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
        if (inputs[1]) {
            setter.call(inputs[1], {$escapedPassword});
            inputs[1].dispatchEvent(new Event('input', { bubbles: true }));
        }
    ");
}

// ─────────────────────────────────────────────────────────────
// Users index
// ─────────────────────────────────────────────────────────────

describe('Users index', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('shows the tabbed users page to an admin', function (): void {
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        loginViaForm($admin->username)
            ->navigate(route('users.index'))
            ->assertRoute('users.index')
            ->assertSee('Admins')
            ->assertSee('Managers')
            ->assertSee('Annotators')
            ->assertNoJavascriptErrors();
    });

    it('redirects unauthenticated users to login', function (): void {
        visit(route('users.index'))
            ->assertRoute('login');
    });
});

// ─────────────────────────────────────────────────────────────
// Annotator creation
// ─────────────────────────────────────────────────────────────

describe('Annotator creation', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AnnotatorPasswordPolicySeeder::class);
    });

    it('allows an admin to create a new annotator', function (): void {
        $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('users.create', ['type' => RolesEnum::ANNOTATOR->value]))
            ->assertSee('Create New Annotator')
            ->assertNoJavascriptErrors();

        $page->type('input[autocomplete="name"]', 'Browser Test Annotator')
            ->type('input[autocomplete="username"]', 'browsertestannotator')
            ->type('input[name="password"]', 'Password1');

        fillPasswordConfirmation($page, 'Password1');

        // The manager row renders as a <label> containing @username — click it to select
        $page->click('text=@' . $manager->username)
            ->wait(0.1);

        $page->press('Create')
            ->assertRoute('users.index')
            ->assertSee('User created successfully')
            ->assertNoJavascriptErrors();

        $this->assertDatabaseHas('users', ['username' => 'browsertestannotator']);
    });
});

// ─────────────────────────────────────────────────────────────
// Annotator editing
// ─────────────────────────────────────────────────────────────

describe('Annotator editing', function (): void {
    beforeEach(function (): void {
        $this->seed(RolesAndPermissionsSeeder::class);
        $this->seed(AnnotatorPasswordPolicySeeder::class);
    });

    it('shows the edit form pre-filled with the annotator data', function (): void {
        $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        AnnotatorOfManager::query()->create(['manager_id' => $manager->id, 'annotator_id' => $annotator->id]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('users.edit', $annotator->id))
            ->assertSee('Edit Annotator')
            ->assertNoJavascriptErrors();

        $nameValue = $page->script("document.querySelector('input[autocomplete=\"name\"]')?.value ?? ''");
        expect($nameValue)->toBe($annotator->name, 'Name input was not pre-filled with the existing value');

        $usernameValue = $page->script("document.querySelector('input[autocomplete=\"username\"]')?.value ?? ''");
        expect($usernameValue)->toBe($annotator->username, 'Username input was not pre-filled');
    });

    it('allows an admin to update an annotator name', function (): void {
        $manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER);
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        AnnotatorOfManager::query()->create(['manager_id' => $manager->id, 'annotator_id' => $annotator->id]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('users.edit', $annotator->id))
            ->assertNoJavascriptErrors();

        // Clear and type a new name — .type() clears before filling (Playwright fill behaviour)
        $page->type('input[autocomplete="name"]', 'Updated Annotator Name')
            ->wait(0.1);

        $page->press('Update User')
            ->assertRoute('users.index')
            ->assertSee('User updated successfully')
            ->assertNoJavascriptErrors();

        $this->assertDatabaseHas('users', ['id' => $annotator->id, 'name' => 'Updated Annotator Name']);
    });
});

// ─────────────────────────────────────────────────────────────
// Admin creation (3-step form)
// ─────────────────────────────────────────────────────────────

describe('Admin creation', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('allows an admin to create a new admin through the 3-step form', function (): void {
        // A project with one annotator assigned — selecting the project locks that annotator,
        // satisfying the "at least 1 annotator" validation on step 2 automatically.
        $project = Project::factory()->create();
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        AnnotatorOfProject::query()->create([
            'project_id' => $project->id,
            'user_id' => $annotator->id,
            'can_flag' => false,
        ]);
        $admin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($admin->username)
            ->navigate(route('users.create', ['type' => RolesEnum::ADMIN->value]))
            ->assertSee('Create New Admin')
            ->assertSee('Personal Info')
            ->assertNoJavascriptErrors();

        // Step 0 — Personal Info
        $page->type('input[autocomplete="name"]', 'New Browser Admin')
            ->type('input[autocomplete="username"]', 'newbrowseradmin')
            ->type('input[autocomplete="email"]', 'newbrowseradmin@example.com')
            ->type('input[name="password"]', 'Password1');
        fillPasswordConfirmation($page, 'Password1');
        $page->wait(0.1)->press('Next');

        // Step 1 — Connect to Projects
        $page->assertSee('Connect to Projects')
            ->assertNoJavascriptErrors();
        $page->click(sprintf('[role="checkbox"][aria-label="%s"]', $project->name))
            ->wait(0.1)
            ->press('Next');

        // Step 2 — Connect to Annotators (annotator locked from the selected project)
        $page->assertSee('Connect to Annotators')
            ->assertNoJavascriptErrors();
        // Use an explicit button selector: ->press() falls back to getByText() which matches the
        // breadcrumb <span> first (same text as the button), so we target <button> explicitly.
        $page->click('button:has-text("Create New Admin")')
            ->assertRoute('users.index')
            ->assertSee('User created successfully')
            ->assertNoJavascriptErrors();

        $this->assertDatabaseHas('users', ['username' => 'newbrowseradmin']);
    });
});

// ─────────────────────────────────────────────────────────────
// Admin editing (3-step form)
// ─────────────────────────────────────────────────────────────

describe('Admin editing', function (): void {
    beforeEach(fn () => $this->seed(RolesAndPermissionsSeeder::class));

    it('shows the 3-step edit form pre-filled with the admin data', function (): void {
        $project = Project::factory()->create();
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        AnnotatorOfProject::query()->create([
            'project_id' => $project->id,
            'user_id' => $annotator->id,
            'can_flag' => false,
        ]);
        $targetAdmin = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        ProjectManager::query()->create(['project_id' => $project->id, 'user_id' => $targetAdmin->id, 'accepted' => true]);
        AnnotatorOfManager::query()->create(['manager_id' => $targetAdmin->id, 'annotator_id' => $annotator->id]);

        $loggedInAdmin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($loggedInAdmin->username)
            ->navigate(route('users.edit', $targetAdmin->id))
            ->assertSee('Edit Admin')
            ->assertNoJavascriptErrors();

        $nameValue = $page->script("document.querySelector('input[autocomplete=\"name\"]')?.value ?? ''");
        expect($nameValue)->toBe($targetAdmin->name, 'Name input was not pre-filled with the existing admin name');
    });

    it('allows an admin to update another admin through the 3-step form', function (): void {
        $project = Project::factory()->create();
        $annotator = User::factory()->create()->assignRole(RolesEnum::ANNOTATOR);
        AnnotatorOfProject::query()->create([
            'project_id' => $project->id,
            'user_id' => $annotator->id,
            'can_flag' => false,
        ]);
        $targetAdmin = User::factory()->create()->assignRole(RolesEnum::ADMIN);
        // Pre-connect the target admin to the project and annotator so the edit form
        // loads with valid pre-selections — this satisfies step 1 and step 2 validation.
        ProjectManager::query()->create(['project_id' => $project->id, 'user_id' => $targetAdmin->id, 'accepted' => true]);
        AnnotatorOfManager::query()->create(['manager_id' => $targetAdmin->id, 'annotator_id' => $annotator->id]);

        $loggedInAdmin = User::factory()->create(['password' => Hash::make('password')])
            ->assignRole(RolesEnum::ADMIN);

        $page = loginViaForm($loggedInAdmin->username)
            ->navigate(route('users.edit', $targetAdmin->id))
            ->assertSee('Edit Admin')
            ->assertNoJavascriptErrors();

        // Step 0 — change the name, leave all other fields (password blank = keep current)
        $page->type('input[autocomplete="name"]', 'Updated Admin Name')
            ->wait(0.1)
            ->press('Next');

        // Step 1 — project pre-selected; Next is already enabled
        $page->assertSee('Connect to Projects')
            ->assertNoJavascriptErrors()
            ->press('Next');

        // Step 2 — annotator locked from the project; submit
        $page->assertSee('Connect to Annotators')
            ->assertNoJavascriptErrors();
        $page->press('Update User')
            ->assertRoute('users.index')
            ->assertSee('User updated successfully')
            ->assertNoJavascriptErrors();

        $this->assertDatabaseHas('users', ['id' => $targetAdmin->id, 'name' => 'Updated Admin Name']);
    });
});
