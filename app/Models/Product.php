<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'model',
        'price_xaf',
        'price_mad',
        'image_url',
        'stock_libreville',
    ];

    protected function casts(): array
    {
        return [
            'price_xaf'        => 'integer',
            'price_mad'        => 'integer',
            'stock_libreville' => 'integer',
        ];
    }

    /** Lignes de commandes API liées à ce produit */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** Lignes de factures ERP liées à ce produit */
    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Retourne le statut de stock calculé pour les badges Bootstrap.
     * En stock (vert) | Stock Faible (jaune) | Rupture (rouge)
     */
    public function getStockStatusAttribute(): string
    {
        if ($this->stock_libreville <= 0) {
            return 'rupture';
        }

        if ($this->stock_libreville <= 3) {
            return 'faible';
        }

        return 'disponible';
    }
}
