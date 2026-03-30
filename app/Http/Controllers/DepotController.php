<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use Illuminate\Http\Request;

class DepotController extends Controller
{
    public function index()
    {
        $depots = Depot::query()
            ->withCount([
                'containers as available_count' => fn ($query) => $query->where('status', 'available'),
                'containers as allocated_count' => fn ($query) => $query->where('status', 'allocated'),
            ])
            ->orderBy('name')
            ->get();

        return view('depots.index', compact('depots'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        Depot::query()->create([
            ...$data,
            'is_active' => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('depots.index')->with('status', 'Deposito criado com sucesso.');
    }

    public function update(Request $request, Depot $depot)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:180'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $depot->update([
            ...$data,
            'is_active' => (bool) ($data['is_active'] ?? false),
        ]);

        return redirect()->route('depots.index')->with('status', 'Deposito atualizado.');
    }

    public function destroy(Depot $depot)
    {
        if ($depot->containers()->exists()) {
            return redirect()
                ->route('depots.index')
                ->withErrors(['depot' => 'Nao e possivel excluir deposito com cacambas vinculadas.']);
        }

        $depot->delete();

        return redirect()->route('depots.index')->with('status', 'Deposito removido.');
    }
}
