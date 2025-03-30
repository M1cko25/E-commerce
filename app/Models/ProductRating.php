<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductRating extends Model
{
    protected $fillable = [
        'customer_id',
        'product_id',
        'rating',
        'review'
    ];

    /**
     * Get the customer who made the rating
     */
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the product that was rated
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
