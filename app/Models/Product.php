<?php

namespace App\Models;

use App\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['company_id', 'name', 'type', 'active'];

    protected function casts(): array
    {
        return ['type' => ProductType::class, 'active' => 'boolean'];
    }

    public function company() { return $this->belongsTo(Company::class); }
    public function prices() { return $this->hasMany(ProductPrice::class); }

    public function getPriceForVehicleType(string $vehicleType): float
    {
        // Usa a coleção já eager-loaded quando disponível; evita 1-2 queries por chamada.
        if ($this->relationLoaded('prices')) {
            $prices = $this->getRelation('prices');
            $price = $prices->firstWhere('vehicle_type', $vehicleType)
                ?? $prices->firstWhere('vehicle_type', 'normal');
            return (float) ($price?->price ?? 0);
        }

        $price = $this->prices()
            ->whereIn('vehicle_type', [$vehicleType, 'normal'])
            ->orderByRaw('CASE WHEN vehicle_type = ? THEN 0 ELSE 1 END', [$vehicleType])
            ->first();

        return (float) ($price?->price ?? 0);
    }

    public function serviceOrders()
    {
        return $this->belongsToMany(ServiceOrder::class, 'service_order_items')
            ->withPivot('quantity', 'unit_price', 'total_price', 'is_primary_wash');
    }
}
