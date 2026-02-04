<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BudgetController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $year = $request->input('year', $now->year);
        $month = $request->input('month', $now->month);

        $budgets = $user->budgets()
            ->with('category')
            ->forMonth($year, $month)
            ->get()
            ->map(function (Budget $budget) {
                return [
                    'id' => $budget->id,
                    'category_id' => $budget->category_id,
                    'category_name' => $budget->category->name,
                    'category_color' => $budget->category->color,
                    'category_icon' => $budget->category->icon,
                    'amount' => $budget->amount,
                    'spent' => $budget->getSpentAmount(),
                    'remaining' => $budget->getRemainingAmount(),
                    'percentage' => $budget->getSpentPercentage(),
                    'status' => $budget->getStatus(),
                    'is_active' => $budget->is_active,
                ];
            });

        $categories = $user->categories()
            ->expense()
            ->active()
            ->ordered()
            ->get();

        return Inertia::render('Budgets/Index', [
            'budgets' => $budgets,
            'categories' => $categories,
            'year' => $year,
            'month' => $month,
        ]);
    }

    public function create(): Response
    {
        $user = auth()->user();
        $now = Carbon::now();

        return Inertia::render('Budgets/Create', [
            'categories' => $user->categories()->expense()->active()->ordered()->get(),
            'year' => $now->year,
            'month' => $now->month,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        $validated = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'amount' => 'required|numeric|min:0.01',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $request->user()->budgets()->updateOrCreate(
            [
                'category_id' => $validated['category_id'],
                'year' => $validated['year'],
                'month' => $validated['month'],
            ],
            ['amount' => $validated['amount']]
        );

        return redirect()->route('budgets.index')->with('success', 'Presupuesto creado correctamente.');
    }

    public function edit(Budget $budget): Response
    {
        $this->authorize('update', $budget);

        $user = auth()->user();

        return Inertia::render('Budgets/Edit', [
            'budget' => $budget,
            'categories' => $user->categories()->expense()->active()->ordered()->get(),
        ]);
    }

    public function update(Request $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget);

        $userId = $request->user()->id;

        $validated = $request->validate([
            'category_id' => ['required', Rule::exists('categories', 'id')->where('user_id', $userId)],
            'amount' => 'required|numeric|min:0.01',
            'year' => 'required|integer|min:2020|max:2100',
            'month' => 'required|integer|min:1|max:12',
        ]);

        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Presupuesto actualizado correctamente.');
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget);

        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Presupuesto eliminado correctamente.');
    }
}
