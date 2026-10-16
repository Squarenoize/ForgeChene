<?php

namespace App\Enum;

enum OrderStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::PENDING => 'En attente',
            self::PROCESSING => 'En cours de traitement',
            self::SHIPPED => 'Expédié',
            self::DELIVERED => 'Livré',
            self::CANCELLED => 'Annulé',
        };
    }
}
