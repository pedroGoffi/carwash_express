<?php

namespace App\Enums;

enum ServiceOrderStatus: string
{
    case QUEUED = 'queued';
    case FINISHED = 'finished';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match($this) {
            self::QUEUED => 'Na Fila',
            self::FINISHED => 'Finalizado',
            self::CANCELLED => 'Cancelada',            
        };
    }
}