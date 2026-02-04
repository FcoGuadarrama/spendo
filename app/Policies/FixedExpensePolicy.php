<?php

namespace App\Policies;

use App\Models\FixedExpense;
use App\Models\User;

class FixedExpensePolicy
{
    public function view(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_id === $user->id;
    }

    public function update(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_id === $user->id;
    }

    public function delete(User $user, FixedExpense $fixedExpense): bool
    {
        return $fixedExpense->user_id === $user->id;
    }
}
