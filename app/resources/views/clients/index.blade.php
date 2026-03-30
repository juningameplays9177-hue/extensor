@extends('layouts.app', ['title' => 'Clientes - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <div>
            <h1 class="title">Gerenciamento de Clientes</h1>
            <p class="subtitle">Cadastre e gerencie os clientes do sistema</p>
        </div>
        <a href="{{ route('clients.create') }}" class="btn btn-primary">Novo Cliente</a>
    </div>

    <div class="card" style="margin-top: 18px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Documento</th>
                        <th>Endereco</th>
                        <th>Locacoes</th>
                        <th style="text-align: right;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td><strong>{{ $client->name }}</strong></td>
                            <td>
                                @if($client->email)
                                    <div style="font-size: 12px; color: #666;">{{ $client->email }}</div>
                                @endif
                                @if($client->phone)
                                    <div style="font-size: 12px; color: #666;">{{ $client->formatted_phone }}</div>
                                @endif
                                @if(!$client->email && !$client->phone)
                                    <span style="color: #999;">-</span>
                                @endif
                            </td>
                            <td>{{ $client->document ?? '-' }}</td>
                            <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $client->address ?? '-' }}
                            </td>
                            <td>
                                <span class="badge">{{ $client->rentals()->count() }}</span>
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('clients.edit', $client) }}" class="btn btn-muted" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                    @if($client->rentals()->count() === 0)
                                        <form method="POST" action="{{ route('clients.destroy', $client) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este cliente?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: var(--muted);">
                                Nenhum cliente cadastrado. <a href="{{ route('clients.create') }}">Cadastre o primeiro cliente</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
