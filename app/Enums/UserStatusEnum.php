<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case ACTIVE = 'active';
    case PENDING = 'pending';
    case SUSPEND = 'suspend';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('Active'),
            self::PENDING => __('Pending'),
            self::SUSPEND => __('Suspend'),
        };
    }
}
