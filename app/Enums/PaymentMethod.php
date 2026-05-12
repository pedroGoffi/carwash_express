<?php

namespace App\Enums;

enum PaymentMethod: string
{
    case CASH = 'cash';
    case CREDIT = 'credit';
    case DEBIT = 'debit';
    case PIX = 'pix';

    public function label(): string
    {
        return match($this) {
            self::CASH => 'Dinheiro',
            self::CREDIT => 'Crédito',
            self::DEBIT => 'Débito',
            self::PIX => 'Pix',
        };
    }
}