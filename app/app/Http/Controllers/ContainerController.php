<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Depot;
use Illuminate\Http\Request;

class ContainerController extends Controller
{
    public function index()
    {
        try {
            $containers = Container::query()
                ->with('depot')
                ->orderBy('identifier')
                ->get();

            $depots = Depot::query()->orderBy('name')->get();

            return view('containers.index', compact('containers', 'depots'));
        } catch (\Exception $e) {
            \Log::error('Erro ao carregar containers', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            
            return view('containers.index', [
                'containers' => collect(),
                'depots' => Depot::query()->orderBy('name')->get(),
            ])->withErrors(['error' => 'Erro ao carregar containers. Verifique os logs.']);
        }
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:80', 'unique:containers,identifier'],
            'depot_id' => ['required', 'exists:depots,id'],
        ]);

        Container::query()->create([
            ...$data,
            'status' => Container::STATUS_AVAILABLE,
        ]);

        return redirect()->route('containers.index')->with('status', 'Cacamba cadastrada.');
    }

    public function update(Request $request, Container $container)
    {
        $data = $request->validate([
            'identifier' => ['required', 'string', 'max:80', 'unique:containers,identifier,'.$container->id],
            'depot_id' => ['required', 'exists:depots,id'],
        ]);

        $container->update($data);

        return redirect()->route('containers.index')->with('status', 'Cacamba atualizada.');
    }

    public function destroy(Container $container)
    {
        if ($container->status === Container::STATUS_ALLOCATED) {
            return redirect()
                ->route('containers.index')
                ->withErrors(['container' => 'Nao e possivel remover uma cacamba alocada.']);
        }

        $container->delete();

        return redirect()->route('containers.index')->with('status', 'Cacamba removida.');
    }
}
