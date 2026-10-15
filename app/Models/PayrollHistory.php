<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PayrollHistory extends Model
{
    protected $fillable = [
        'company_id', 'user_id', 'period', 'base_amount', 'advances_amount',
        'consumptions_amount', 'extra_costs_amount', 'employer_cost_amount', 'net_amount', 'days_worked', 'closed_by', 'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'period' => 'date',
            'base_amount' => 'decimal:2',
            'advances_amount' => 'decimal:2',
            'consumptions_amount' => 'decimal:2',
            'net_amount' => 'decimal:2',
            'extra_costs_amount' => 'decimal:2',
            'employer_cost_amount' => 'decimal:2',
            'closed_at' => 'datetime',
        ];
    }

    public function user() { return $this->belongsTo(User::class); }
    public function closedBy() { return $this->belongsTo(User::class, 'closed_by'); }
}
