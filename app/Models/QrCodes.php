<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QrCodes extends Model
{
    /** @use HasFactory<\Database\Factories\QrCodesFactory> */
    use HasFactory;
    protected $fillable = ['qr_path'];
}
