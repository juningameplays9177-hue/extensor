@extends('layouts.app', ['title' => 'Depositos - Top Rio'])

@section('content')
    <h1 class="title">Depositos e estoque</h1>
    <p class="subtitle">Cadastro de depositos e controle de disponibilidade por unidade.</p>

    <div class="card" style="margin-top: 18px;">
        <h3>Novo deposito</h3>
        <form method="POST" action="{{ route('depots.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Nome</label>
                    <input id="name" name="name" required>
                </div>
                <div>
                    <label for="address">Endereco</label>
                    <input id="address" name="address">
                </div>
                <div>
                    <label for="is_active">Status</label>
                    <select id="is_active" name="is_active">
                        <option value="1">Ativo</option>
                        <option value="0">Inativo</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 12px;">Salvar deposito</button>
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Depositos cadastrados</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Deposito</th>
                        <th>Endereco</th>
                        <th>Disponiveis</th>
                        <th>Alocadas</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($depots as $depot)
                    <tr>
                        <td>
                            <form method="POST" action="{{ route('depots.update', $depot) }}">
                                @csrf
                                @method('PUT')
                                <input name="name" value="{{ $depot->name }}" style="margin-bottom: 8px;">
                                <input name="address" value="{{ $depot->address }}" placeholder="Endereco">
                                <select name="is_active" style="margin-top: 8px;">
                                    <option value="1" @selected($depot->is_active)>Ativo</option>
                                    <option value="0" @selected(! $depot->is_active)>Inativo</option>
                                </select>
                                <div style="margin-top: 8px;">
                                    <button class="btn btn-muted" type="submit">Atualizar</button>
                                </div>
                            </form>
                        </td>
                        <td>{{ $depot->address ?: '-' }}</td>
                        <td><span class="badge ok">{{ $depot->available_count }}</span></td>
                        <td><span class="badge">{{ $depot->allocated_count }}</span></td>
                        <td>{{ $depot->is_active ? 'Ativo' : 'Inativo' }}</td>
                        <td>
                            <form class="inline" method="POST" action="{{ route('depots.destroy', $depot) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Nenhum deposito cadastrado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
