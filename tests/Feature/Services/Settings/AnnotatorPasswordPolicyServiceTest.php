<?php

declare(strict_types=1);

use App\Enums\PasswordCompositionEnum;
use App\Models\AnnotatorPasswordPolicy;
use App\Services\Settings\AnnotatorPasswordPolicyService;
use Database\Seeders\AnnotatorPasswordPolicySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->seed(AnnotatorPasswordPolicySeeder::class);
    $this->service = resolve(AnnotatorPasswordPolicyService::class);
});

describe('AnnotatorPasswordPolicyService', function (): void {
    it('returns the seeded policy', function (): void {
        $policy = $this->service->getPolicy();

        expect($policy->min_length)->toBe(8)
            ->and($policy->composition_mode)->toBe(PasswordCompositionEnum::LETTERS_AND_NUMBERS)
            ->and($policy->mixed_case_required)->toBeFalse();
    });

    it('populates the cache on first call so subsequent reads do not query the database', function (): void {
        // First call populates the cache
        $this->service->getPolicy();

        // Second call should come from cache — verify by updating the DB directly
        // (no cache bust) and confirming the stale value is still returned
        AnnotatorPasswordPolicy::query()->update(['min_length' => 99]);

        $cachedPolicy = $this->service->getPolicy();

        expect($cachedPolicy->min_length)->toBe(8);
    });

    it('builds a Password rule instance', function (): void {
        $rule = $this->service->buildRule();

        expect($rule)->toBeInstanceOf(Password::class);
    });

    it('respects min_length when building the rule', function (): void {
        AnnotatorPasswordPolicy::query()->update(['min_length' => 10, 'composition_mode' => PasswordCompositionEnum::NO_RESTRICTION->value]);
        Cache::forget('annotator_password_policy');

        $rule = $this->service->buildRule();

        // 9-char password should fail, 10-char should pass
        $fail = validator(['p' => 'aaaaaaaaa'], ['p' => [$rule]]);
        $pass = validator(['p' => 'aaaaaaaaaa'], ['p' => [$rule]]);

        expect($fail->fails())->toBeTrue()
            ->and($pass->passes())->toBeTrue();
    });

    it('enforces letter requirement for letters_only composition', function (): void {
        AnnotatorPasswordPolicy::query()->update([
            'min_length' => 4,
            'composition_mode' => PasswordCompositionEnum::LETTERS_ONLY->value,
        ]);
        Cache::forget('annotator_password_policy');

        $rule = $this->service->buildRule();

        $fail = validator(['p' => '1234'], ['p' => [$rule]]);
        $pass = validator(['p' => 'abcd'], ['p' => [$rule]]);

        expect($fail->fails())->toBeTrue()
            ->and($pass->passes())->toBeTrue();
    });

    it('enforces mixed case when required', function (): void {
        AnnotatorPasswordPolicy::query()->update([
            'min_length' => 4,
            'composition_mode' => PasswordCompositionEnum::NO_RESTRICTION->value,
            'mixed_case_required' => true,
        ]);
        Cache::forget('annotator_password_policy');

        $rule = $this->service->buildRule();

        $fail = validator(['p' => 'alllower'], ['p' => [$rule]]);
        $pass = validator(['p' => 'MixedCase'], ['p' => [$rule]]);

        expect($fail->fails())->toBeTrue()
            ->and($pass->passes())->toBeTrue();
    });

    it('persists updated policy to the database', function (): void {
        $this->service->updatePolicy([
            'min_length' => 20,
            'composition_mode' => PasswordCompositionEnum::LETTERS_NUMBERS_SYMBOLS->value,
            'mixed_case_required' => true,
        ]);

        $policy = AnnotatorPasswordPolicy::query()->first();
        expect($policy->min_length)->toBe(20)
            ->and($policy->composition_mode)->toBe(PasswordCompositionEnum::LETTERS_NUMBERS_SYMBOLS)
            ->and($policy->mixed_case_required)->toBeTrue();
    });

    it('busts the cache after updating the policy', function (): void {
        Cache::spy();

        $this->service->updatePolicy([
            'min_length' => 10,
            'composition_mode' => PasswordCompositionEnum::LETTERS_ONLY->value,
            'mixed_case_required' => false,
        ]);

        Cache::shouldHaveReceived('forget')->with('annotator_password_policy')->once();
    });

    it('returns the refreshed policy after an update', function (): void {
        $returned = $this->service->updatePolicy([
            'min_length' => 16,
            'composition_mode' => PasswordCompositionEnum::LETTERS_AND_NUMBERS->value,
            'mixed_case_required' => false,
        ]);

        expect($returned->min_length)->toBe(16);
    });
});
