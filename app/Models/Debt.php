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
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'monthly_payment' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
        'closed_at' => 'datetime',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
}
