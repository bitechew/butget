<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Butget extends Model
{
    use HasFactory;

    protected $fillable = [
        'monthly_income'
    ];
}
