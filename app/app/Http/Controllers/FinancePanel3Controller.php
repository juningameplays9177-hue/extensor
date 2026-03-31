<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Receivable;
use Carbon\Carbon;
use Illuminate\View\View;

class FinancePanel3Controller extends Controller
{
    public function index(): View
    {
        $totalIn = (float) Receivable::query()
            ->where('status', Receivable::STATUS_PAID)
            ->sum('value');

        $totalOut = (float) Expense::query()
            ->where('status', Expense::STATUS_PAID)
            ->sum('value');

        $cashBalance = $totalIn - $totalOut;

        $startDate = now()->subDays(13)->startOfDay();
        $endDate = now()->endOfDay();

        $inByDate = Receivable::query()
            ->where('status', Receivable::STATUS_PAID)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as dt, SUM(value) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt');

        $outByDate = Expense::query()
            ->where('status', Expense::STATUS_PAID)
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->selectRaw('DATE(payment_date) as dt, SUM(value) as total')
            ->groupBy('dt')
            ->pluck('total', 'dt');

        $labels = [];
        $dailyIn = [];
        $dailyOut = [];

        for ($cursor = $startDate->copy(); $cursor->lte($endDate); $cursor->addDay()) {
            $key = $cursor->format('Y-m-d');
            $labels[] = $cursor->format('d/m');
            $dailyIn[] = (float) ($inByDate[$key] ?? 0);
            $dailyOut[] = (float) ($outByDate[$key] ?? 0);
        }

        $peopleWhoOwe = Receivable::query()
            ->with('rental.client')
            ->where('status', Receivable::STATUS_PENDING)
            ->orderBy('due_date')
            ->get()
            ->map(function (Receivable $receivable) {
                $name = $receivable->rental && $receivable->rental->client
                    ? $receivable->rental->client->name
                    : ($receivable->description ?: 'Sem nome');

                return [
                    'name' => $name,
                    'value' => (float) $receivable->value,
                    'date' => $receivable->due_date
                        ? Carbon::parse($receivable->due_date)->format('d/m/Y')
                        : '-',
                ];
            });

        return view('finance.panel-3', compact(
            'totalIn',
            'totalOut',
            'cashBalance',
            'labels',
            'dailyIn',
            'dailyOut',
            'peopleWhoOwe'
        ));
    }
}
