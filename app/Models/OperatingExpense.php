<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Custos fixos/variaveis da empresa, separados das despesas de colaboradores. */
class OperatingExpense extends Model
{
    protected $fillable = ['company_id', 'category', 'description', 'amount', 'date', 'created_by'];

    protected function casts(): array
    {
        return ['date' => 'date', 'amount' => 'decimal:2'];
    }

    public const CATEGORIES = [
        'electricity' => 'Energia elétrica',
        'water' => 'Água e esgoto',
        'rent' => 'Aluguel',
        'materials' => 'Materiais e produtos de limpeza',
        'maintenance' => 'Manutenção e reparos',
        'internet' => 'Internet e telefone',
        'taxes' => 'Impostos e taxas',
        'other' => 'Outros custos',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
