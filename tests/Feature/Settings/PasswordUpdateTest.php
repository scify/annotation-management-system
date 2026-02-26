<?php

declare(strict_types=1);

use App\Enums\RolesEnum;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    // Run the role seeder first
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('password can be updated', function (): void {
    $user = User::factory()->create()->assignRole(RolesEnum::ADMINISTRATOR->value);

    $url = 'settings/password';
    // Get the password update page first to establish session
    $this->actingAs($user)->get($url);

    $response = $this->actingAs($user)
        ->from($url)
        ->put($url, [
            'current_password' => 'password',
            'password' => 'new-password123',
            'password_confirmation' => 'new-password123',
            '_token' => session('_token'),
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect($url);

    expect(Hash::check('new-password123', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function (): void {
    $user = User::factory()->create()->assignRole(RolesEnum::ADMINISTRATOR->value);

    $url = 'settings/password';
    $this->actingAs($user)->get($url);

    $response = $this
        ->actingAs($user)
        ->from($url)
        ->put($url, [
            'current_password' => 'wrong-password',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
            '_token' => session('_token'),
        ]);

    $response
        ->assertSessionHasErrors('current_password')
        ->assertRedirect($url);
});
