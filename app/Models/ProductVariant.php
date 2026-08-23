<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'type', 'value', 'sku', 'price', 'stock', 'sort_order'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return [
            'price'      => 'decimal:2',
            'stock'      => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(Cart::class);
    }
}
