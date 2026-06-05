<?php

declare(strict_types=1);

namespace App\Http\Controllers;

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
}
