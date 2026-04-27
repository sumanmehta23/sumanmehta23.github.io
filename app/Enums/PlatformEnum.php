<?php

namespace App\Enums;

enum PlatformEnum: string
{
    /**
     * Platform constants for the 'platform' field
     */
    case MT5 = 'mt5';
    case X9 = 'x9';

    /**
     * Get the trade_platform value for this platform
     * (Used in queries filtering by trade_platform column)
     */
    public function tradePlatform(): string
    {
        return match ($this) {
            self::MT5 => 'MetaTrader5',
            self::X9 => 'x9',
        };
    }

    /**
     * Get all available platforms
     */
    public static function all(): array
    {
        return [
            self::MT5->value,
            self::X9->value,
        ];
    }

    /**
     * Check if a value is a valid platform
     */
    public static function isValid(?string $value): bool
    {
        return in_array($value, self::all(), strict: true);
    }

    /**
     * Get the enum case from a string value
     * Returns null for empty/invalid values (unlike built-in from() which throws)
     */
    public static function fromValue(?string $value): ?self
    {
        if (empty($value)) {
            return null;
        }

        foreach (self::cases() as $case) {
            if ($case->value === $value) {
                return $case;
            }
        }

        return null;
    }

    /**
     * Get friendly display name
     */
    public function displayName(): string
    {
        return match ($this) {
            self::MT5 => 'MT5',
            self::X9 => 'X9',
        };
    }
}
