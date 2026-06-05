<?php

declare(strict_types=1);

namespace App\Services\Settings;

use App\Enums\PasswordCompositionEnum;
use App\Models\AnnotatorPasswordPolicy;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rules\Password;

readonly class AnnotatorPasswordPolicyService {
    private const string CACHE_KEY = 'annotator_password_policy';

    private const int CACHE_TTL = 3600;

    public function getPolicy(): AnnotatorPasswordPolicy {
        $cached = Cache::get(self::CACHE_KEY);

        // Guard against stale cache entries that stored the Eloquent model directly.
        // If the entry is not a plain array, bust it so the closure below re-populates correctly.
        if (! is_array($cached)) {
            Cache::forget(self::CACHE_KEY);
            $cached = null;
        }

        if ($cached === null) {
            $record = AnnotatorPasswordPolicy::query()->firstOrFail();
            $cached = [
                'min_length' => $record->min_length,
                'composition_mode' => $record->composition_mode->value,
                'mixed_case_required' => $record->mixed_case_required,
            ];
            Cache::put(self::CACHE_KEY, $cached, self::CACHE_TTL);
        }

        /** @var array{min_length: int, composition_mode: string, mixed_case_required: bool} $cached */
        $policy = new AnnotatorPasswordPolicy();
        $policy->fill($cached);

        return $policy;
    }

    public function buildRule(): Password {
        $policy = $this->getPolicy();

        $rule = Password::min($policy->min_length);

        $rule = match ($policy->composition_mode) {
            PasswordCompositionEnum::NO_RESTRICTION => $rule,
            PasswordCompositionEnum::LETTERS_ONLY => $rule->letters(),
            PasswordCompositionEnum::LETTERS_AND_NUMBERS => $rule->letters()->numbers(),
            PasswordCompositionEnum::LETTERS_NUMBERS_SYMBOLS => $rule->letters()->numbers()->symbols(),
        };

        if ($policy->mixed_case_required) {
            return $rule->mixedCase();
        }

        return $rule;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updatePolicy(array $data): AnnotatorPasswordPolicy {
        $policy = AnnotatorPasswordPolicy::query()->firstOrFail();

        $policy->update($data);

        Cache::forget(self::CACHE_KEY);

        return $policy->refresh();
    }
}
