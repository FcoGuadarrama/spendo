<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Debt;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DebtController extends Controller
{
    public function index(Request $request): Response
    {
        $debts = $request->user()->debts()
            ->orderBy('remaining_amount', 'desc')
            ->get()
            ->map(function ($debt) {
                return [
                    'id' => $debt->id,
                    'name' => $debt->name,
                    'total_amount' => $debt->total_amount,
                    'remaining_amount' => $debt->remaining_amount,
                    'paid_percentage' => $debt->total_amount > 0
                        ? round((($debt->total_amount - $debt->remaining_amount) / $debt->total_amount) * 100, 1)
                        : 0,
                    'monthly_payment' => $debt->monthly_payment,
                    'next_payment_date' => $this->calculateNextPaymentDate($debt),
                ];
            });

        return Inertia::render('Debts/Index', [
            'debts' => $debts,
        ]);
    }

    public function create(): Response
    {
        $creditCards = auth()->user()->accounts()->where('type', 'credit_card')->orderBy('name')->get();

        return Inertia::render('Debts/Create', [
            'creditCards' => $creditCards,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:loan,credit_card',
            'account_id' => 'nullable|required_if:type,credit_card|exists:accounts,id',
            'total_amount' => 'required|numeric|min:0.01',
            'remaining_amount' => 'required|numeric|min:0|lte:total_amount',
            'monthly_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'total_installments' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $request->user()->debts()->create($validated);

        return redirect()->route('debts.index')->with('success', 'Deuda creada correctamente.');
    }

    public function edit(Debt $debt): Response
    {
        $this->authorize('update', $debt);

        $creditCards = auth()->user()->accounts()->where('type', 'credit_card')->orderBy('name')->get();

        return Inertia::render('Debts/Edit', [
            'debt' => $debt,
            'creditCards' => $creditCards,
        ]);
    }

    public function update(Request $request, Debt $debt): RedirectResponse
    {
        $this->authorize('update', $debt);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:loan,credit_card',
            'account_id' => 'nullable|required_if:type,credit_card|exists:accounts,id',
            'total_amount' => 'required|numeric|min:0.01',
            'remaining_amount' => 'required|numeric|min:0|lte:total_amount',
            'monthly_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'due_day' => 'nullable|integer|min:1|max:31',
            'total_installments' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        $debt->update($validated);

        return redirect()->route('debts.index')->with('success', 'Deuda actualizada correctamente.');
    }

    public function destroy(Debt $debt): RedirectResponse
    {
        $this->authorize('delete', $debt);

        $debt->delete();

        return redirect()->route('debts.index')->with('success', 'Deuda eliminada correctamente.');
    }

    private function calculateNextPaymentDate(Debt $debt): ?string
    {
        if (!$debt->due_day) {
            return null;
        }

        $today = now();
        $paymentDate = $today->copy()->day($debt->due_day);

        if ($today->day > $debt->due_day) {
            $paymentDate->addMonth();
        }

        return $paymentDate->toDateString();
    }
}
