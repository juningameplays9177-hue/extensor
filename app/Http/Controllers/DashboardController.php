<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Depot;
use App\Models\Rental;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $activeRentals = Rental::query()
            ->with('container')
            ->where('status', Rental::STATUS_ACTIVE)
            ->orderBy('allocated_at')
            ->get();

        $stats = [
            'depots' => Depot::query()->count(),
            'containers_total' => Container::query()->count(),
            'containers_available' => Container::query()->available()->count(),
            'containers_allocated' => Container::query()->where('status', Container::STATUS_ALLOCATED)->count(),
            'alerts_24h' => $activeRentals->filter(fn (Rental $rental) => $rental->elapsed_hours >= 24 && $rental->elapsed_hours < 48)->count(),
            'alerts_48h' => $activeRentals->filter(fn (Rental $rental) => $rental->elapsed_hours >= 48)->count(),
        ];

        return view('dashboard', [
            'activeRentals' => $activeRentals,
            'stats' => $stats,
        ]);
    }
}
