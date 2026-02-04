<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();

        $transactions = $user->transactions()
            ->with(['account', 'category'])
            ->when($request->input('search'), function ($query, $search) {
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($request->input('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when($request->input('account_id'), function ($query, $accountId) {
                $query->where('account_id', $accountId);
            })
            ->when($request->input('category_id'), function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($request->input('date_from'), function ($query, $dateFrom) {
                $query->where('date', '>=', $dateFrom);
            })
            ->when($request->input('date_to'), function ($query, $dateTo) {
                $query->where('date', '<=', $dateTo);
            })
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $accounts = $user->accounts()->active()->orderBy('name')->get();
        $categories = $user->categories()->active()->ordered()->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'accounts' => $accounts,
            'categories' => $categories,
            'filters' => $request->only(['search', 'type', 'account_id', 'category_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();

        return Inertia::render('Transactions/Create', [
            'accounts' => $user->accounts()->active()->orderBy('name')->get(),
            'categories' => $user->categories()->active()->ordered()->get(),
            'debts' => $user->debts()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'transfer_to_account_id' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $userId), 'different:account_id'],
            'debt_id' => ['nullable', Rule::exists('debts', 'id')->where('user_id', $userId)],
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'is_confirmed' => 'boolean',
        ]);

        $request->user()->transactions()->create($validated);

        return redirect()->route('transactions.index')->with('success', 'Transacción creada correctamente.');
    }

    public function edit(Transaction $transaction): Response
    {
        $this->authorize('update', $transaction);

        $user = auth()->user();

        return Inertia::render('Transactions/Edit', [
            'transaction' => $transaction,
            'accounts' => $user->accounts()->active()->orderBy('name')->get(),
            'categories' => $user->categories()->active()->ordered()->get(),
            'debts' => $user->debts()->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Transaction $transaction): RedirectResponse
    {
        $this->authorize('update', $transaction);

        $userId = $request->user()->id;

        $validated = $request->validate([
            'account_id' => ['required', Rule::exists('accounts', 'id')->where('user_id', $userId)],
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'transfer_to_account_id' => ['nullable', Rule::exists('accounts', 'id')->where('user_id', $userId), 'different:account_id'],
            'debt_id' => ['nullable', Rule::exists('debts', 'id')->where('user_id', $userId)],
            'type' => 'required|in:income,expense,transfer',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'date' => 'required|date',
            'time' => 'nullable|date_format:H:i',
            'is_confirmed' => 'boolean',
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')->with('success', 'Transacción actualizada correctamente.');
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return redirect()->route('transactions.index')->with('success', 'Transacción eliminada correctamente.');
    }
}
