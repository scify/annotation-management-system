<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

abstract class Controller {
    protected function dumpDebugJson(mixed $data, string $filename): void {
        if (! config('app.debug')) {
            return;
        }

        $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if (is_string($json)) {
            Storage::disk('local')->put($filename, $json);
        }
    }

    /**
     * Success JSON for AJAX mutations. An optional message is surfaced as a flash
     * toast by the frontend `apiFetchWithFlash`/`showFlashToasts`; any extra payload
     * (e.g. a created record) is merged alongside it.
     *
     * @param  array<string, mixed>  $data
     */
    protected function jsonSuccess(?string $message = null, array $data = [], int $status = 200): JsonResponse {
        return response()->json(
            $message === null ? $data : [...$data, 'success' => $message],
            $status,
        );
    }

    /**
     * Error JSON for AJAX mutations. The message is shown as an error toast and stays
     * inspectable in devtools (unlike a 302 redirect with an empty body).
     * An optional machine-readable $code is included so the frontend can branch on
     * specific failure reasons without parsing translated strings.
     */
    protected function jsonError(string $message, int $status = 422, ?string $code = null): JsonResponse {
        $body = ['error' => $message];
        if ($code !== null) {
            $body['code'] = $code;
        }

        return response()->json($body, $status);
    }
}
