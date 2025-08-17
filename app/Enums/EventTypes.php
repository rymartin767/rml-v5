<?php

namespace App\Enums;

enum EventTypes: string
{
    case PERSONAL = 'personal';
    case WORK = 'work';
    case SOCIAL = 'social';
    case FAMILY = 'family';

    public function label(): string
    {
        return match ($this) {
            self::PERSONAL => 'Personal',
            self::WORK => 'Work',
            self::SOCIAL => 'Social',
            self::FAMILY => 'Family',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PERSONAL => 'info',
            self::WORK => 'danger',
            self::SOCIAL => 'warning',
            self::FAMILY => 'success',
        };
    }
}
