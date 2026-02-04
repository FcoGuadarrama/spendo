<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Budget extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'period',
        'year',
        'month',
        'is_active',
        'notify_at_threshold',
        'threshold_percentage',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'year' => 'integer',
        'month' => 'integer',
        'is_active' => 'boolean',
        'notify_at_threshold' => 'boolean',
        'threshold_percentage' => 'integer',
    ];

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    // ─────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->where('year', $year)->where('month', $month);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeCurrent($query)
    {
        $now = now();
        return $query->where('year', $now->year)->where('month', $now->month);
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    public function getSpentAmount(): float
    {
        return $this->category->getTotalSpent($this->year, $this->month);
    }

    public function getRemainingAmount(): float
    {
        return max(0, $this->amount - $this->getSpentAmount());
    }

    public function getSpentPercentage(): float
    {
        if ($this->amount <= 0) {
            return 0;
        }

        return min(100, ($this->getSpentAmount() / $this->amount) * 100);
    }

    public function isOverBudget(): bool
    {
        return $this->getSpentAmount() > $this->amount;
    }

    public function isAtThreshold(): bool
    {
        return $this->getSpentPercentage() >= $this->threshold_percentage;
    }

    public function getStatus(): string
    {
        if ($this->isOverBudget()) {
            return 'over';
        }

        if ($this->isAtThreshold()) {
            return 'warning';
        }

        return 'ok';
    }
}
