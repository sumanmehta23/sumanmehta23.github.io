<?php

namespace App\Enums;

enum WithdrawalStatusEnum: int
{
    case PENDING = 0;
    case COMPLETED = 1;
    case REJECTED = 2;
    case CANCELLED = 3;

    // String constants for API responses
    public const PENDING_LABEL = 'Pending';

    public const COMPLETED_LABEL = 'Completed';

    public const REJECTED_LABEL = 'Rejected from API';

    public const CANCELLED_LABEL = 'Cancelled by client';

    /**
     * Get the label for the withdrawal status
     */
    public function label(): string
    {
        return match ($this) {
            self::PENDING => self::PENDING_LABEL,
            self::COMPLETED => self::COMPLETED_LABEL,
            self::REJECTED => self::REJECTED_LABEL,
            self::CANCELLED => self::CANCELLED_LABEL,
        };
    }

    /**
     * Get all values as an array
     */
    public static function toArray(): array
    {
        return [
            self::PENDING->value => self::PENDING_LABEL,
            self::COMPLETED->value => self::COMPLETED_LABEL,
            self::REJECTED->value => self::REJECTED_LABEL,
            self::CANCELLED->value => self::CANCELLED_LABEL,
        ];
    }

    /**
     * Check if status is pending
     */
    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    /**
     * Check if status is completed
     */
    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    /**
     * Check if status is rejected
     */
    public function isRejected(): bool
    {
        return $this === self::REJECTED;
    }

    /**
     * Check if status is cancelled
     */
    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }
}
