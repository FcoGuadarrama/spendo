<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Account extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'balance',
        'credit_limit',
        'currency',
        'color',
        'icon',
        'is_active',
        'include_in_total',
        'closing_day',
        'due_day',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_active' => 'boolean',
        'include_in_total' => 'boolean',
        'closing_day' => 'integer',
        'due_day' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(Transaction::class, 'transfer_to_account_id');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeIncludedInTotal($query)
    {
        return $query->where('include_in_total', true);
    }

    public function scopeCreditCards($query)
    {
        return $query->where('type', 'credit_card');
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    public function isCreditCard(): bool
    {
        return $this->type === 'credit_card';
    }

    public function getAvailableCredit(): ?float
    {
        if (!$this->isCreditCard() || !$this->credit_limit) {
            return null;
        }

        return $this->credit_limit - abs($this->balance);
    }

    public function updateBalance(): void
    {
        $initialBalance = $this->getOriginal('balance') ?? 0;

        if ($this->transactions()->exists() || $this->incomingTransfers()->exists()) {
            $income = $this->transactions()
                ->where('type', 'income')
                ->where('is_confirmed', true)
                ->sum('amount');

            $expenses = $this->transactions()
                ->where('type', 'expense')
                ->where('is_confirmed', true)
                ->sum('amount');

            $transfersIn = Transaction::where('transfer_to_account_id', $this->id)
                ->where('type', 'transfer')
                ->where('is_confirmed', true)
                ->sum('amount');

            $transfersOut = $this->transactions()
                ->where('type', 'transfer')
                ->where('is_confirmed', true)
                ->sum('amount');

            $this->balance = $initialBalance + $income - $expenses + $transfersIn - $transfersOut;
        }

        $this->saveQuietly();
    }

    public function adjustBalance(float $amount, string $type): void
    {
        if ($type === 'income' || $type === 'transfer_in') {
            $this->balance += $amount;
        } elseif ($type === 'expense' || $type === 'transfer') {
            $this->balance -= $amount;
        }

        $this->saveQuietly();
    }

}
