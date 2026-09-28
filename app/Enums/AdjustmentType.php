<?php

namespace App\Enums;

enum AdjustmentType: string
{
    case Addition = 'Added';
    case Subtraction = 'Removed';

    public function label(): string
    {
        return match($this) {
            self::Addition => 'Addition (+)',
            self::Subtraction => 'Subtraction (-)',
        };
    }
}
