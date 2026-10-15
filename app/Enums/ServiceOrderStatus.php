<?php

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case WAITING = 'waiting';
    case QUEUED = 'queued';
    case FINISHED = 'finished';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::WAITING => 'Em espera',
            self::QUEUED => 'Na fila',
            self::FINISHED => 'Concluído · aguardando chaves',
            self::DELIVERED => 'Entregue',
            self::CANCELLED => 'Cancelada',
        };
    }

    public function badge(): string
    {
        return match($this) {
            self::WAITING => 'amber',
            self::QUEUED => 'blue',
            self::FINISHED => 'emerald',
            self::DELIVERED => 'slate',
            self::CANCELLED => 'red',
        };
    }
}
