<?php

namespace App\Enums;

enum BlogPostStatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case DRAFT = 'draft';

    public static function toArray(): array
    {
        return [
            self::DRAFT->value => 'Draft',
            self::ACTIVE->value => 'Active',
            self::INACTIVE->value => 'Inactive',
        ];
    }

    public static function getLabel(string $value): string
    {
        return self::toArray()[$value];
    }

    public static function getColorClass(string $value): string
    {
        $colors = [
            self::DRAFT->value => 'dark',
            self::ACTIVE->value => 'success',
            self::INACTIVE->value => 'light',
        ];
        return $colors[$value];
    }
}

