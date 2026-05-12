<?php

namespace App\Enums;

enum ProductType: string
{
    case WASH = 'wash';
    case DRINK = 'drink';
    case SNACK = 'snack';
    case OTHER = 'other';

    public function label(): string
    {
        return match($this) {
            self::WASH => 'Lavagem',
            self::DRINK => 'Bebida',
            self::SNACK => 'Comida',
            self::OTHER => 'Outro',
        };
    }
}