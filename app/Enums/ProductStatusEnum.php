<?php

namespace App\Enums;

enum ProductStatusEnum: string
{
    case IN_STOCK = 'In Stock';
    case LOW_STOCK = 'Low Stock';
    case NO_STOCK = 'Out of Stock';

    public function label(): string
    {
        return match ($this) {
            self::IN_STOCK => __('In Stock'),
            self::LOW_STOCK => __('Low Stock'),
            self::NO_STOCK => __('Out of Stock'),
        };
    }

    public function getClass(): string
    {
        return match ($this) {
            self::IN_STOCK => 'bg-green-100/30! text-green-800 border-green-300',
            self::LOW_STOCK => 'bg-yellow-100/30! text-yellow-800 border-yellow-300',
            self::NO_STOCK => 'bg-red-100/30! text-red-800 border-red-300',
        };
    }
}
