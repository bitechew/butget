<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'from_account_id', 'to_account_id', 'description', 'amount', 'type', 'category',
        'date', 'notes', 'is_recurring', 'recurrence_interval', 'recurring_parent_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fromAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'from_account_id');
    }

    public function toAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'to_account_id');
    }

    public function recurringParent(): BelongsTo
    {
        return $this->belongsTo(Transaction::class, 'recurring_parent_id');
    }

    public function recurringChildren()
    {
        return $this->hasMany(Transaction::class, 'recurring_parent_id');
    }
}