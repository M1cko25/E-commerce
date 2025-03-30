<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WishlistItem extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'added_at'
    ];

    protected $casts = [
        'added_at' => 'datetime'
    ];

    /**
     * Get the product in the wishlist
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer who owns the wishlist item
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
