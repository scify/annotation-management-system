<?php

declare(strict_types=1);

use App\Enums\PasswordCompositionEnum;
use App\Enums\RolesEnum;
use App\Models\AnnotatorPasswordPolicy;
use App\Models\User;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(RolesAndPermissionsSeeder::class);
    $this->seed(AnnotatorPasswordPolicySeeder::class);

    $this->admin = User::factory()->create()->assignRole(RolesEnum::ADMIN->value)->load('roles');
    $this->manager = User::factory()->create()->assignRole(RolesEnum::ANNOTATION_MANAGER->value)->load('roles');
});

describe('AnnotatorPasswordPolicyController', function (): void {
    it('allows admin to view the policy settings page', function (): void {
        $this->actingAs($this->admin)
            ->get(route('settings.annotator-password-policy.edit'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('settings/annotator-password-policy')
                ->has('policy')
                ->has('composition_modes')
                ->where('policy.min_length', 8)
                ->where('policy.composition_mode', PasswordCompositionEnum::LETTERS_AND_NUMBERS->value)
                ->where('policy.mixed_case_required', false)
            );
    });

    it('returns 403 for annotation managers', function (): void {
        $this->actingAs($this->manager)
            ->get(route('settings.annotator-password-policy.edit'))
            ->assertForbidden();
    });

    it('returns 403 for unauthenticated users', function (): void {
        $this->get(route('settings.annotator-password-policy.edit'))
            ->assertRedirect(route('login'));
    });

    it('allows admin to update the policy', function (): void {
        // Act
        $this->actingAs($this->admin)
            ->put(route('settings.annotator-password-policy.update'), [
                'min_length' => 12,
                'composition_mode' => PasswordCompositionEnum::LETTERS_NUMBERS_SYMBOLS->value,
                'mixed_case_required' => true,
            ])
            ->assertRedirect(route('settings.annotator-password-policy.edit'))
            ->assertSessionHasNoErrors();

        // Assert
        $policy = AnnotatorPasswordPolicy::query()->first();
        expect($policy->min_length)->toBe(12)
            ->and($policy->composition_mode)->toBe(PasswordCompositionEnum::LETTERS_NUMBERS_SYMBOLS)
            ->and($policy->mixed_case_required)->toBeTrue();
    });

    it('rejects min_length below 4', function (): void {
        $this->actingAs($this->admin)
            ->put(route('settings.annotator-password-policy.update'), [
                'min_length' => 3,
                'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
                'mixed_case_required' => false,
            ])
            ->assertSessionHasErrors('min_length');
    });

    it('rejects min_length above 128', function (): void {
        $this->actingAs($this->admin)
            ->put(route('settings.annotator-password-policy.update'), [
                'min_length' => 129,
                'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
                'mixed_case_required' => false,
            ])
            ->assertSessionHasErrors('min_length');
    });

    it('rejects an invalid composition_mode value', function (): void {
        $this->actingAs($this->admin)
            ->put(route('settings.annotator-password-policy.update'), [
                'min_length' => 8,
                'composition_mode' => 'made_up_value',
                'mixed_case_required' => false,
            ])
            ->assertSessionHasErrors('composition_mode');
    });

    it('forbids annotation managers from updating the policy', function (): void {
        $this->actingAs($this->manager)
            ->put(route('settings.annotator-password-policy.update'), [
                'min_length' => 10,
                'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
                'mixed_case_required' => false,
            ])
            ->assertForbidden();
    });
});
