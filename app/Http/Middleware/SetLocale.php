<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale {
    private const array SUPPORTED_LOCALES = ['en', 'el'];

    public function handle(Request $request, Closure $next): Response {
        $locale = $request->cookie('locale');

        if (is_string($locale) && in_array($locale, self::SUPPORTED_LOCALES, strict: true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
