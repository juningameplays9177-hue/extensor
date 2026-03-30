<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExpenseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Expense::query()->orderBy('due_date', 'asc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('due_date', $request->date);
        }

        $expenses = $query->get();
        
        $stats = [
            'total_pending' => Expense::where('status', Expense::STATUS_PENDING)->sum('value'),
            'total_paid' => Expense::where('status', Expense::STATUS_PAID)->sum('value'),
            'overdue_count' => Expense::where('status', Expense::STATUS_PENDING)
                ->where('due_date', '<', now()->startOfDay())
                ->count(),
        ];

        return view('expenses.index', compact('expenses', 'stats'));
    }

    public function create(): View
    {
        return view('expenses.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === Expense::STATUS_PAID && !$request->has('payment_date')) {
            $data['payment_date'] = now();
        }

        Expense::create($data);

        return redirect()->route('expenses.index')->with('status', 'Conta a pagar criada com sucesso.');
    }

    public function edit(Expense $expense): View
    {
        return view('expenses.edit', compact('expense'));
    }

    public function update(Request $request, Expense $expense): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'payment_date' => ['nullable', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === Expense::STATUS_PAID && empty($data['payment_date'])) {
            $data['payment_date'] = $expense->payment_date ?? now();
        } elseif ($data['status'] === Expense::STATUS_PENDING) {
            $data['payment_date'] = null;
        }

        $expense->update($data);

        return redirect()->route('expenses.index')->with('status', 'Conta a pagar atualizada com sucesso.');
    }

    public function destroy(Expense $expense): RedirectResponse
    {
        $expense->delete();

        return redirect()->route('expenses.index')->with('status', 'Conta a pagar excluida com sucesso.');
    }

    public function markAsPaid(Expense $expense): RedirectResponse
    {
        $expense->update([
            'status' => Expense::STATUS_PAID,
            'payment_date' => $expense->payment_date ?? now(),
        ]);

        return redirect()->route('expenses.index')->with('status', 'Conta marcada como paga.');
    }
}
