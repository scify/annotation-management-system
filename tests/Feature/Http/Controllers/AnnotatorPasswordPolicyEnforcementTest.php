<?php

declare(strict_types=1);

use App\Enums\PasswordCompositionEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotatorPasswordPolicy;
use App\Models\User;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(AnnotatorPasswordPolicySeeder::class);

    $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN->value)->load('roles');
    $this->manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value)->load('roles');
});

/**
 * Helper: update the policy row and bust the cache so the service reads fresh.
 *
 * @param  array<string, mixed>  $attributes
 */
function setPolicy(array $attributes): void {
    AnnotatorPasswordPolicy::query()->update($attributes);
    Cache::forget('annotator_password_policy');
}

describe('Annotator password policy enforcement on create', function (): void {
    it('rejects a password shorter than the configured minimum', function (): void {
        setPolicy(['min_length' => 12]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_short',
                'password' => 'Ab1!xyz',   // 7 chars — below min 12
                'password_confirmation' => 'Ab1!xyz',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasErrors('password');
    });

    it('accepts a password that meets the policy', function (): void {
        setPolicy([
            'min_length' => 8,
            'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
            'mixed_case_required' => false,
        ]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_ok',
                'password' => 'letters123',
                'password_confirmation' => 'letters123',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));
    });

    it('rejects a letters-only password when composition requires letters and numbers', function (): void {
        setPolicy([
            'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
        ]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_nonum',
                'password' => 'onlyletters',
                'password_confirmation' => 'onlyletters',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasErrors('password');
    });

    it('enforces mixed case when required', function (): void {
        setPolicy([
            'min_length' => 8,
            'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
            'mixed_case_required' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_nocase',
                'password' => 'alllower123',   // no uppercase
                'password_confirmation' => 'alllower123',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasErrors('password');
    });

    it('accepts a password that satisfies the mixed case requirement', function (): void {
        setPolicy([
            'min_length' => 8,
            'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
            'mixed_case_required' => true,
        ]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_mixed',
                'password' => 'MixedCase123',
                'password_confirmation' => 'MixedCase123',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));
    });

    it('accepts any password when composition is no_restriction', function (): void {
        setPolicy([
            'min_length' => 4,
            'composition_mode' => PasswordCompositionEnum::NO_RESTRICTION->value,
            'mixed_case_required' => false,
        ]);

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'type' => RolesEnum::ANNOTATOR->value,
                'name' => 'Test Annotator',
                'username' => 'test_annotator_any',
                'password' => '1234',   // digits only — no restriction
                'password_confirmation' => '1234',
                'manager_ids' => [$this->manager->id],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('users.index'));
    });
});
