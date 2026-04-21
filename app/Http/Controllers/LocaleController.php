<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocaleController extends Controller {
    private const array SUPPORTED_LOCALES = ['en', 'el'];

    public function update(Request $request): RedirectResponse {
        $validated = $request->validate([
            'locale' => ['required', 'string', Rule::in(self::SUPPORTED_LOCALES)],
        ]);

        /** @var string $locale */
        $locale = $validated['locale'];

        return back()->withCookie(
            cookie('locale', $locale, 60 * 24 * 365, secure: $request->secure(), sameSite: 'Lax')
        );
    }
}
