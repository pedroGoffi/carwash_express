<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'legal_name',
        'document',
    ];



    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}