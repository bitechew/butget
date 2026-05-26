<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\HasMany;

use App\Models\PaymentMethod;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function transactions()
    {
        return $this->hasMany(\App\Models\Transaction::class);
    }

    public function budgets()
    {
        return $this->hasMany(\App\Models\Budget::class);
    }

    public function goals()
    {
        return $this->hasMany(\App\Models\Goal::class);
    }

    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function accounts()
    {
        return $this->hasMany(\App\Models\Account::class);
    }

    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }
}
