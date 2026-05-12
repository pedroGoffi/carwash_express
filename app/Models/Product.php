<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'type',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'type' => ProductType::class,
            'active' => 'boolean',
        ];
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function getPriceForVehicleType(string $vehicleType): float
    {
        $price = $this->prices()->where('vehicle_type', $vehicleType)->first();
        
        if (!$price) {
            $price = $this->prices()->where('vehicle_type', 'normal')->first();
        }
        
        return $price ? $price->price : 0;
    }

    public function serviceOrders()
    {
        return $this->belongsToMany(ServiceOrder::class, 'service_order_items')
            ->withPivot('quantity', 'unit_price', 'total_price');
    }
}