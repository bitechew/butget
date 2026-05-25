<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

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

    public function accounts()
    {
        return $this->hasMany(\App\Models\Account::class);
    }

    public function categories()
    {
        return $this->hasMany(\App\Models\Category::class);
    }
}
