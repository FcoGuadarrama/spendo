<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Debt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'total_amount',
        'remaining_amount',
        'monthly_payment',
        'start_date',
        'end_date',
        'due_day',
        'total_installments',
        'notes',
        'closed_at',
        'type',
        'account_id',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
        'account_id' => 'integer',
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

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    public function updateBalance(): void
    {
        $paidAmount = $this->transactions()
            ->where('is_confirmed', true)
            ->sum('amount');

        // Logic: Total Amount - Paid Amount = Remaining Amount
        // Assuming transactions linked to debt are PAYMENTS towards it.
        $this->remaining_amount = $this->total_amount - $paidAmount;

        if ($this->remaining_amount <= 0) {
            $this->remaining_amount = 0;
            if (!$this->closed_at) {
                $this->closed_at = now();
            }
        } else {
            $this->closed_at = null;
        }

        $this->saveQuietly();
    }

    /**
     * Calculate monthly payment based on total amount and installments
     * Call this after updating total_amount or total_installments
     */
    public function calculateMonthlyPayment(): void
    {
        if ($this->total_installments && $this->total_installments > 0) {
            $this->monthly_payment = $this->total_amount / $this->total_installments;
            $this->saveQuietly();
        }
    }

    /**
     * Recalculate monthly payment based on remaining amount and remaining months
     */
    public function recalculateMonthlyPayment(): void
    {
        if (!$this->end_date) {
            return;
        }

        $today = now()->startOfDay();
        $endDate = $this->end_date->startOfDay();

        if ($today >= $endDate) {
            $this->monthly_payment = $this->remaining_amount;
            $this->saveQuietly();
            return;
        }

        $monthsRemaining = max(1, $today->diffInMonths($endDate) + 1);
        $this->monthly_payment = $this->remaining_amount / $monthsRemaining;
        $this->saveQuietly();
    }
}
