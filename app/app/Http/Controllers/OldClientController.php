<?php

namespace App\Http\Controllers;

use App\Models\OldClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OldClientController extends Controller
{
    public function index(): View
    {
        $oldClients = OldClient::query()->orderBy('checked')->orderByDesc('created_at')->get();

        return view('old-clients.index', compact('oldClients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'amount_due' => ['required', 'numeric', 'min:0'],
        ]);

        OldClient::create($data);

        return redirect()->route('old-clients.index')->with('status', 'Cliente antigo adicionado com sucesso.');
    }

    public function toggleChecked(OldClient $old_client): RedirectResponse
    {
        $old_client->update([
            'checked' => !$old_client->checked,
        ]);

        return redirect()->route('old-clients.index')->with('status', 'Checklist atualizado.');
    }

    public function destroy(OldClient $old_client): RedirectResponse
    {
        $old_client->delete();

        return redirect()->route('old-clients.index')->with('status', 'Cliente removido com sucesso.');
    }
}
