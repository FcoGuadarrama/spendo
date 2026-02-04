<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'account_id',
        'category_id',
        'transfer_to_account_id',
        'type',
        'amount',
        'description',
        'notes',
        'date',
        'time',
        'is_recurring',
        'recurring_frequency',
        'recurring_end_date',
        'is_confirmed',
        'reference',
        'tags',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'date',
        'time' => 'datetime:H:i',
        'recurring_end_date' => 'date',
        'is_recurring' => 'boolean',
        'is_confirmed' => 'boolean',
        'tags' => 'array',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function transferToAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'transfer_to_account_id');
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeIncome($query)
    {
        return $query->where('type', 'income');
    }

    public function scopeExpense($query)
    {
        return $query->where('type', 'expense');
    }

    public function scopeTransfer($query)
    {
        return $query->where('type', 'transfer');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_confirmed', false);
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeForPeriod($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('date', [$start, $end]);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('date', $year)->whereMonth('date', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->whereYear('date', $year);
    }

    public function scopeRecent($query, int $limit = 10)
    {
        return $query->orderBy('date', 'desc')->orderBy('created_at', 'desc')->limit($limit);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    public function isIncome(): bool
    {
        return $this->type === 'income';
    }

    public function isExpense(): bool
    {
        return $this->type === 'expense';
    }

    public function isTransfer(): bool
    {
        return $this->type === 'transfer';
    }

    public function getSignedAmount(): float
    {
        return $this->isExpense() ? -$this->amount : $this->amount;
    }

    public function getFormattedAmount(): string
    {
        $prefix = $this->isExpense() ? '-' : '+';
        return $prefix . '$' . number_format($this->amount, 2);
    }

    // ─────────────────────────────────────────────────────────────
    // Events
    // ─────────────────────────────────────────────────────────────

    protected static function booted(): void
    {
        static::created(function (Transaction $transaction) {
            $transaction->account->updateBalance();
            
            if ($transaction->isTransfer() && $transaction->transferToAccount) {
                $transaction->transferToAccount->updateBalance();
            }
        });

        static::updated(function (Transaction $transaction) {
            $transaction->account->updateBalance();
            
            if ($transaction->isTransfer() && $transaction->transferToAccount) {
                $transaction->transferToAccount->updateBalance();
            }
        });

        static::deleted(function (Transaction $transaction) {
            $transaction->account->updateBalance();
            
            if ($transaction->isTransfer() && $transaction->transferToAccount) {
                $transaction->transferToAccount->updateBalance();
            }
        });
    }
}
