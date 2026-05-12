<?php

namespace App\Enums;

enum VehicleType: string
{
    case NORMAL = 'normal';
    case SUV = 'suv';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match($this) {
            self::NORMAL => 'Carro Pequeno',
            self::SUV => 'SUV / Picape',
            self::PICKUP => 'Caminhonete',
        };
    }
}