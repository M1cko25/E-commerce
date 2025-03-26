<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromptHistory extends Model
{
    /** @use HasFactory<\Database\Factories\PromptHistoryFactory> */
    use HasFactory;

    protected $fillable = ['title', 'user_id'];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(Customer::class, 'user_id');
    }
}
