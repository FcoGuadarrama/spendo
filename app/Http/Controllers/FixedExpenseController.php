<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\FixedExpense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FixedExpenseController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        
        $fixedExpenses = $user->fixedExpenses()
            ->with('category')
            ->orderBy('day_of_month')
            ->get();

        $categories = $user->categories()->expense()->active()->ordered()->get();

        return Inertia::render('FixedExpenses/Index', [
            'fixedExpenses' => $fixedExpenses,
            'categories' => $categories,
        ]);
    }

    public function create(): Response
    {
         $user = auth()->user();
         return Inertia::render('FixedExpenses/Create', [
             'categories' => $user->categories()->expense()->active()->ordered()->get(),
         ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'day_of_month' => 'required|integer|min:1|max:31',
            'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'is_active' => 'boolean',
        ]);

        $request->user()->fixedExpenses()->create($validated);

        return redirect()->route('fixed-expenses.index')->with('success', 'Gasto fijo creado correctamente.');
    }

    public function edit(FixedExpense $fixedExpense): Response
    {
        $this->authorize('update', $fixedExpense);
        
        $user = auth()->user();

        return Inertia::render('FixedExpenses/Edit', [
            'fixedExpense' => $fixedExpense,
            'categories' => $user->categories()->expense()->active()->ordered()->get(),
        ]);
    }

    public function update(Request $request, FixedExpense $fixedExpense): RedirectResponse
    {
        $this->authorize('update', $fixedExpense);
        $userId = $request->user()->id;

        $validated = $request->validate([
             'description' => 'required|string|max:255',
             'amount' => 'required|numeric|min:0.01',
             'day_of_month' => 'required|integer|min:1|max:31',
             'category_id' => ['nullable', Rule::exists('categories', 'id')->where('user_id', $userId)],
             'is_active' => 'boolean',
        ]);

        $fixedExpense->update($validated);

        return redirect()->route('fixed-expenses.index')->with('success', 'Gasto fijo actualizado correctamente.');
    }

    public function destroy(FixedExpense $fixedExpense): RedirectResponse
    {
        $this->authorize('delete', $fixedExpense);
        $fixedExpense->delete();
        
        return redirect()->route('fixed-expenses.index')->with('success', 'Gasto fijo eliminado correctamente.');
    }
}
