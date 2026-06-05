<?php

declare(strict_types=1);

namespace App\Enums;

enum PasswordCompositionEnum: string {
    case NO_RESTRICTION = 'no_restriction';
    case LETTERS_ONLY = 'letters_only';
    case LETTERS_AND_NUMBERS = 'letters_and_numbers';
    case LETTERS_NUMBERS_SYMBOLS = 'letters_numbers_and_symbols';

    public function label(): string {
        return match ($this) {
            self::NO_RESTRICTION => 'No restriction',
            self::LETTERS_ONLY => 'Letters only',
            self::LETTERS_AND_NUMBERS => 'Letters and numbers',
            self::LETTERS_NUMBERS_SYMBOLS => 'Letters, numbers and symbols',
        };
    }
}
