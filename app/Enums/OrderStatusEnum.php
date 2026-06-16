<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';

    // Opcional: método para obtener etiquetas para el frontend
    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('Pending'),
            self::SHIPPED => __('Shipped'),
            self::DELIVERED => __('Delivered'),
        };
    }
}
