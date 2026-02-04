<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AccountController extends Controller
{
    public function index(Request $request): Response
    {
        $accounts = $request->user()
            ->accounts()
            ->withCount('transactions')
            ->orderBy('name')
            ->get()
            ->map(function ($account) {
                $data = [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                    'currency' => $account->currency,
                    'color' => $account->color,
                    'icon' => $account->icon,
                    'is_active' => $account->is_active,
                    'transactions_count' => $account->transactions_count,
                ];

                // Agregar información de tarjeta de crédito si es aplicable
                if ($account->isCreditCard()) {
                    $data['credit_limit'] = $account->credit_limit;
                    $data['statement_balance'] = $account->statement_balance;
                    $data['available_credit'] = $account->getAvailableCreditBalance();
                    $data['credit_utilization'] = $account->getCreditUtilization();
                    $data['monthly_payment'] = $account->getTotalMonthlyPayment();
                    $data['total_debt'] = $account->getTotalOutstandingDebt();
                    $data['due_day'] = $account->due_day;
                    $data['closing_day'] = $account->closing_day;
                }

                return $data;
            });

        return Inertia::render('Accounts/Index', [
            'accounts' => $accounts,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Accounts/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,credit_card,cash,investment',
            'balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'include_in_total' => 'boolean',
        ]);

        $request->user()->accounts()->create($validated);

        return redirect()->route('accounts.index')->with('success', 'Cuenta creada correctamente.');
    }

    public function edit(Account $account): Response
    {
        $this->authorize('update', $account);

        return Inertia::render('Accounts/Edit', [
            'account' => $account,
        ]);
    }

    public function update(Request $request, Account $account): RedirectResponse
    {
        $this->authorize('update', $account);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:checking,savings,credit_card,cash,investment',
            'balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric|min:0',
            'currency' => 'required|string|size:3',
            'color' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'include_in_total' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $account->update($validated);

        return redirect()->route('accounts.index')->with('success', 'Cuenta actualizada correctamente.');
    }

    public function destroy(Account $account): RedirectResponse
    {
        $this->authorize('delete', $account);

        $account->delete();

        return redirect()->route('accounts.index')->with('success', 'Cuenta eliminada correctamente.');
    }
}
