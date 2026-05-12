<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'password',
        'role',
        'monthly_salary',    // Salário mensal (mensalista)
        'daily_rate',        // Valor por dia (diarista)
        'days_worked',       // Dias trabalhados no mês
        'total_advances',    // Total de vales no mês
        'total_consumptions',

        'total_to_receive',  // Valor a receber (salário - vales)
        'active',
        'hired_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'password' => 'hashed',
            'monthly_salary' => 'decimal:2',
            'daily_rate' => 'decimal:2',
            'days_worked' => 'integer',
            'total_advances' => 'decimal:2',
            'total_to_receive' => 'decimal:2',
            'active' => 'boolean',
            'hired_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function advances()
    {
        return $this->hasMany(Advance::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::ADMIN;
    }

    public function isManager(): bool
    {
        return $this->role === UserRole::MANAGER;
    }

    public function isOperator(): bool
    {
        return $this->role === UserRole::OPERATOR;
    }

    /**
     * Calcular folha de pagamento do funcionário
     */
    public function calculatePayroll()
    {
        // Calcular salário base
        if ($this->daily_rate > 0) {
            // Diarista
            $salary = $this->daily_rate * ($this->days_worked ?? 0);
        } else {
            // Mensalista
            $salary = $this->monthly_salary ?? 0;
        }
        
        // Total de vales do mês atual
        $totalAdvances = $this->advances()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');
        
        // Valor a receber
        $totalToReceive = $salary - $totalAdvances;
        
        // Atualizar campos
        $this->update([
            'total_advances' => $totalAdvances,
            'total_to_receive' => $totalToReceive,
        ]);
        
        return [
            'salary' => $salary,
            'advances' => $totalAdvances,
            'to_receive' => $totalToReceive
        ];
    }
}