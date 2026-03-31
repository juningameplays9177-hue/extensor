<?php

namespace App\Http\Controllers;

use App\Models\DailySaldoGastoItem;
use App\Models\Expense;
use Illuminate\Support\Facades\Schema;
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

        $saldoRaw = $request->query('saldo_date');
        $saldoDate = is_string($saldoRaw) && strtotime($saldoRaw) !== false
            ? date('Y-m-d', strtotime($saldoRaw))
            : now()->format('Y-m-d');

        if (Schema::hasTable('daily_saldo_gastos_items')) {
            $saldoGastosItems = DailySaldoGastoItem::query()
                ->whereDate('ref_date', $saldoDate)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            $saldoGastosTotal = (float) $saldoGastosItems->sum('value');
        } else {
            $saldoGastosItems = collect();
            $saldoGastosTotal = 0.0;
        }

        return view('expenses.index', compact(
            'expenses',
            'stats',
            'saldoDate',
            'saldoGastosItems',
            'saldoGastosTotal'
        ));
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

    public function storeSaldoGastoDia(Request $request): RedirectResponse
    {
        if (!Schema::hasTable('daily_saldo_gastos_items')) {
            return redirect()->route('expenses.index')->with('status', 'Execute a migration para habilitar a declaracao de saldo.');
        }

        $data = $request->validate([
            'ref_date' => ['required', 'date'],
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['value'] = $data['value'] ?? 0;
        $data['sort_order'] = (int) DailySaldoGastoItem::whereDate('ref_date', $data['ref_date'])->max('sort_order') + 1;

        DailySaldoGastoItem::create($data);

        return $this->redirectExpensesWithSaldo($request, 'Lancamento adicionado na declaracao de saldo.');
    }

    public function updateSaldoGastoDia(Request $request, DailySaldoGastoItem $daily_saldo_gasto_item): RedirectResponse
    {
        if (!Schema::hasTable('daily_saldo_gastos_items')) {
            return redirect()->route('expenses.index')->with('status', 'Tabela da declaracao de saldo nao encontrada.');
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);
        $data['value'] = $data['value'] ?? 0;

        $daily_saldo_gasto_item->update($data);

        return $this->redirectExpensesWithSaldo($request, 'Lancamento atualizado.');
    }

    public function destroySaldoGastoDia(Request $request, DailySaldoGastoItem $daily_saldo_gasto_item): RedirectResponse
    {
        if (!Schema::hasTable('daily_saldo_gastos_items')) {
            return redirect()->route('expenses.index')->with('status', 'Tabela da declaracao de saldo nao encontrada.');
        }

        $ref = $daily_saldo_gasto_item->ref_date->format('Y-m-d');
        $daily_saldo_gasto_item->delete();

        $request->merge(['saldo_date' => $ref]);

        return $this->redirectExpensesWithSaldo($request, 'Lancamento removido.');
    }

    private function redirectExpensesWithSaldo(Request $request, string $message): RedirectResponse
    {
        $q = [];
        if ($request->filled('status')) {
            $q['status'] = $request->input('status');
        }
        if ($request->filled('date')) {
            $q['date'] = $request->input('date');
        }
        if ($request->filled('saldo_date')) {
            $q['saldo_date'] = $request->input('saldo_date');
        } elseif ($request->filled('ref_date')) {
            $q['saldo_date'] = $request->input('ref_date');
        }

        return redirect()->route('expenses.index', $q)->with('status', $message);
    }
}
