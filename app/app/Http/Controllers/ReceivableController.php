<?php

namespace App\Http\Controllers;

use App\Models\Receivable;
use App\Models\Rental;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReceivableController extends Controller
{
    public function index(Request $request): View
    {
        $query = Receivable::query()->with('rental.client')->orderBy('due_date', 'asc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('date')) {
            $query->whereDate('due_date', $request->date);
        }

        $receivables = $query->get();
        
        $stats = [
            'total_pending' => Receivable::where('status', Receivable::STATUS_PENDING)->sum('value'),
            'total_paid' => Receivable::where('status', Receivable::STATUS_PAID)->sum('value'),
            'overdue_count' => Receivable::where('status', Receivable::STATUS_PENDING)
                ->where('due_date', '<', now()->startOfDay())
                ->count(),
        ];

        return view('receivables.index', compact('receivables', 'stats'));
    }

    public function create(): View
    {
        $rentals = Rental::query()
            ->where('status', Rental::STATUS_ACTIVE)
            ->with('client')
            ->orderBy('allocated_at', 'desc')
            ->get();

        return view('receivables.create', compact('rentals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rental_id' => ['nullable', 'exists:rentals,id'],
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === Receivable::STATUS_PAID && !$request->has('payment_date')) {
            $data['payment_date'] = now();
        }

        Receivable::create($data);

        return redirect()->route('receivables.index')->with('status', 'Conta a receber criada com sucesso.');
    }

    public function edit(Receivable $receivable): View
    {
        $rentals = Rental::query()
            ->where('status', Rental::STATUS_ACTIVE)
            ->with('client')
            ->orderBy('allocated_at', 'desc')
            ->get();

        return view('receivables.edit', compact('receivable', 'rentals'));
    }

    public function update(Request $request, Receivable $receivable): RedirectResponse
    {
        $data = $request->validate([
            'rental_id' => ['nullable', 'exists:rentals,id'],
            'description' => ['required', 'string', 'max:255'],
            'value' => ['required', 'numeric', 'min:0'],
            'due_date' => ['required', 'date'],
            'payment_date' => ['nullable', 'date'],
            'receipt_number' => ['nullable', 'string', 'max:50'],
            'invoice_number' => ['nullable', 'string', 'max:50'],
            'status' => ['required', 'in:pending,paid'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($data['status'] === Receivable::STATUS_PAID && empty($data['payment_date'])) {
            $data['payment_date'] = $receivable->payment_date ?? now();
        } elseif ($data['status'] === Receivable::STATUS_PENDING) {
            $data['payment_date'] = null;
        }

        $receivable->update($data);

        return redirect()->route('receivables.index')->with('status', 'Conta a receber atualizada com sucesso.');
    }

    public function destroy(Receivable $receivable): RedirectResponse
    {
        $receivable->delete();

        return redirect()->route('receivables.index')->with('status', 'Conta a receber excluida com sucesso.');
    }

    public function markAsPaid(Receivable $receivable): RedirectResponse
    {
        $receivable->update([
            'status' => Receivable::STATUS_PAID,
            'payment_date' => $receivable->payment_date ?? now(),
        ]);

        return redirect()->route('receivables.index')->with('status', 'Conta marcada como recebida.');
    }
}
