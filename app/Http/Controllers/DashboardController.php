<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Budget;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $year = $request->input('year', $now->year);
        $month = $request->input('month', $now->month);

        // Get accounts with balances
        $accounts = $user->accounts()
            ->active()
            ->orderBy('name')
            ->get();

        // Monthly stats
        $monthlyIncome = $user->transactions()
            ->income()
            ->forMonth($year, $month)
            ->confirmed()
            ->sum('amount');

        $monthlyExpenses = $user->transactions()
            ->expense()
            ->forMonth($year, $month)
            ->confirmed()
            ->sum('amount');

        // Expenses by category for the month
        $expensesByCategory = $user->transactions()
            ->expense()
            ->forMonth($year, $month)
            ->confirmed()
            ->with('category')
            ->get()
            ->groupBy('category_id')
            ->map(function ($transactions, $categoryId) {
                $category = $transactions->first()->category;
                return [
                    'category_id' => $categoryId,
                    'category_name' => $category?->name ?? 'Sin categoría',
                    'category_color' => $category?->color ?? '#9ca3af',
                    'category_icon' => $category?->icon ?? 'help-circle',
                    'total' => $transactions->sum('amount'),
                    'count' => $transactions->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        // Recent transactions
        $recentTransactions = $user->transactions()
            ->with(['account', 'category'])
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Budgets with progress
        $budgets = $user->budgets()
            ->with('category')
            ->forMonth($year, $month)
            ->active()
            ->get()
            ->map(function (Budget $budget) {
                return [
                    'id' => $budget->id,
                    'category_name' => $budget->category->name,
                    'category_color' => $budget->category->color,
                    'category_icon' => $budget->category->icon,
                    'amount' => $budget->amount,
                    'spent' => $budget->getSpentAmount(),
                    'remaining' => $budget->getRemainingAmount(),
                    'percentage' => $budget->getSpentPercentage(),
                    'status' => $budget->getStatus(),
                ];
            });

        // Monthly trend (last 6 months)
        $monthlyTrend = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = $now->copy()->subMonths($i);
            $monthlyTrend->push([
                'month' => $date->format('M'),
                'year' => $date->year,
                'income' => $user->transactions()
                    ->income()
                    ->forMonth($date->year, $date->month)
                    ->confirmed()
                    ->sum('amount'),
                'expenses' => $user->transactions()
                    ->expense()
                    ->forMonth($date->year, $date->month)
                    ->confirmed()
                    ->sum('amount'),
            ]);
        }

        // Debts Separation
        $allActiveDebts = $user->debts()
            ->where('remaining_amount', '>', 0)
            ->orderBy('remaining_amount', 'desc')
            ->get();

        $personalDebts = $allActiveDebts->where('type', 'loan');
        $creditCardDebts = $allActiveDebts->where('type', 'credit_card');

        // Totals
        $totalPersonalDebt = $personalDebts->sum('remaining_amount');
        $totalPersonalMonthly = $personalDebts->sum('monthly_payment');

        $totalCCDebt = $creditCardDebts->sum('remaining_amount'); // MSI remaining
        $totalCCMonthly = $creditCardDebts->sum('monthly_payment'); // MSI monthly payment

        // 1. Fixed Expenses (Manual Projections)
        $rawFixed = $user->fixedExpenses()->where('is_active', true)->get();
        \Illuminate\Support\Facades\Log::info('Fixed Expenses Count: ' . $rawFixed->count());
        
        $fixedExpenses = $rawFixed->map(function ($expense) {
                return [
                    'id' => 'fe-' . $expense->id,
                    'name' => $expense->description,
                    'amount' => (float) $expense->amount,
                    'day' => $expense->day_of_month,
                    'type' => 'fixed_expense',
                    'icon' => $expense->category?->icon ?? 'calendar',
                    'color' => $expense->category?->color ?? 'blue',
                ];
            });

        // 2. Personal Debts (Fixed Due Day)
        $personalDebtsProjection = $personalDebts->map(function ($debt) {
            return [
                'id' => $debt->id,
                'name' => $debt->name,
                'amount' => $debt->monthly_payment,
                'day' => $debt->due_day ?? 1, // Default to 1st if not set
                'type' => 'loan',
                'icon' => 'banknote',
                'color' => 'red',
            ];
        });

        // 3. Credit Card Debts (Based on Account Due Day)
        // Group by Account to show "Payment for Card X" instead of individual MSI items?
        // Or show individual MSI items?
        // User asked for "Cuanto voy a pagar este mes".
        // Usually you pay the TOTAL of the card.
        // Let's group CC debts by Account.
        $ccDebtsProjection = $creditCardDebts->groupBy('account_id')->map(function ($debts, $accountId) use ($accounts) {
             $account = $accounts->firstWhere('id', $accountId);
             return [
                'id' => 'cc-' . $accountId,
                'name' => 'Pago ' . ($account?->name ?? 'Tarjeta'),
                'amount' => $debts->sum('monthly_payment'),
                'day' => $account?->due_day ?? 1,
                'type' => 'credit_card',
                'icon' => 'credit-card',
                'color' => 'orange',
             ];
        });

        $monthlyCalendar = $fixedExpenses
            ->concat($personalDebtsProjection)
            ->concat($ccDebtsProjection)
            ->sortBy('day')
            ->values();

        // Debugging Sums
        $totalFixedExpenses = $fixedExpenses->sum('amount');
        $totalDebtsProjection = $personalDebtsProjection->sum('amount');
        $totalCCProjection = $ccDebtsProjection->sum('amount');
        
        // Force recalculation of total (sanity check)
        $totalMonthlyCommitment = $totalFixedExpenses + $totalDebtsProjection + $totalCCProjection;

        \Illuminate\Support\Facades\Log::info("Fixed: $totalFixedExpenses, Debts: $totalDebtsProjection, CC: $totalCCProjection, Total: $totalMonthlyCommitment");

        return Inertia::render('Dashboard', [
            'accounts' => $accounts,
            'totalBalance' => $user->getTotalBalance(),
            'monthlyIncome' => $monthlyIncome,
            'monthlyExpenses' => $monthlyExpenses,
            'monthlySavings' => $monthlyIncome - $monthlyExpenses,
            'expensesByCategory' => $expensesByCategory,
            'recentTransactions' => $recentTransactions,
            'budgets' => $budgets,
            'monthlyTrend' => $monthlyTrend,
            'currentMonth' => $now->format('F Y'),
            'year' => $year,
            'month' => $month,
            'debts' => $allActiveDebts->values(),
            'totalPersonalDebt' => $totalPersonalDebt,
            'totalPersonalMonthly' => $totalPersonalMonthly,
            'totalCCDebt' => $totalCCDebt,
            'totalCCMonthly' => $totalCCMonthly,
            'monthlyCalendar' => $monthlyCalendar,
            'totalMonthlyCommitment' => $totalMonthlyCommitment,
            'debugTotalFixed' => $totalFixedExpenses, // Sending explicitly
        ]);
    }
}
