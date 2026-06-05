<?php

declare(strict_types=1);

namespace App\Http\Controllers\Settings;

use App\Enums\PasswordCompositionEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateAnnotatorPasswordPolicyRequest;
use App\Services\Settings\AnnotatorPasswordPolicyService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class AnnotatorPasswordPolicyController extends Controller {
    public function __construct(
        private readonly AnnotatorPasswordPolicyService $policyService,
    ) {}

    public function edit(): Response {
        $policy = $this->policyService->getPolicy();

        return Inertia::render('settings/annotator-password-policy', [
            'policy' => [
                'min_length' => $policy->min_length,
                'composition_mode' => $policy->composition_mode->value,
                'mixed_case_required' => $policy->mixed_case_required,
            ],
            'composition_modes' => collect(PasswordCompositionEnum::cases())
                ->map(fn (PasswordCompositionEnum $case): array => [
                    'value' => $case->value,
                    'label' => $case->label(),
                ])
                ->values()
                ->all(),
        ]);
    }

    public function update(UpdateAnnotatorPasswordPolicyRequest $request): RedirectResponse {
        $this->policyService->updatePolicy($request->validated());

        return to_route('settings.annotator-password-policy.edit')
            ->with('success', __('settings.annotator_password_policy.saved'));
    }
}
