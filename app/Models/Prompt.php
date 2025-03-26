<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Prompt extends Model
{
    /** @use HasFactory<\Database\Factories\PromptFactory> */
    use HasFactory;
    protected $fillable = ['prompt', 'response', 'history_id'];

    public function history()
    {
        return $this->belongsTo(PromptHistory::class);
    }
}
