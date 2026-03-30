<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::query()->orderBy('name')->get();
        return view('clients.index', compact('clients'));
    }

    public function create(): View
    {
        return view('clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        Client::create($data);

        return redirect()->route('clients.index')->with('status', 'Cliente criado com sucesso.');
    }

    public function edit(Client $client): View
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'document' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
        ]);

        $client->update($data);

        return redirect()->route('clients.index')->with('status', 'Cliente atualizado com sucesso.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        if ($client->rentals()->count() > 0) {
            return redirect()->route('clients.index')->withErrors([
                'client' => 'Nao e possivel excluir cliente que possui locacoes associadas.',
            ]);
        }

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente excluido com sucesso.');
    }
}
