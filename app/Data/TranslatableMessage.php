<?php

declare(strict_types=1);

namespace App\Data;

final class TranslatableMessage {
    /**
     * @param  array<string, string|int>  $params
     */
    public static function encode(string $key, array $params = []): string {
        return json_encode(['key' => $key, 'params' => $params], JSON_THROW_ON_ERROR);
    }
}
