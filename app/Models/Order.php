<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'firebase_id',
        'product_id',
        'product_name',
        'payment_status',
        'customer_name',
        'customer_whatsapp',
        'address_libreville',
        'status',
        'total_amount',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'integer',
        ];
    }

    /** Incidents liés à cette commande */
    public function incidents(): HasMany
    {
        return $this->hasMany(Incident::class);
    }

    /** Lignes de produits de cette commande */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
