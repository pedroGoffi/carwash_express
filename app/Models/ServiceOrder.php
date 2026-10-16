<?php

namespace App\Models;

use App\Enums\ServiceOrderStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'customer_name',
        'license_plate',
        'code',
        'customer_type',
        'customer_document',
        'contact_name',
        'vehicle_type',
        'status',
        'total',
        'queued_at',
        'finished_at',
        'delivered_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ServiceOrderStatus::class,
            'total' => 'decimal:2',
            'queued_at' => 'datetime',
            'finished_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'service_order_items')
            ->withPivot('quantity', 'unit_price', 'total_price', 'is_primary_wash');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public static function generateCode(): string
    {
        $last = self::whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->latest('id')
            ->first(['code']);
        
        $number = $last ? intval(substr($last->code, -4)) + 1 : 1;
        
        return 'OS' . now()->format('ym') . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function finish()
    {
        $this->update([
            'status' => ServiceOrderStatus::FINISHED,
            'finished_at' => now(),
        ]);
    }
}