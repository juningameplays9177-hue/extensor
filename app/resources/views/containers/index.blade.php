@extends('layouts.app', ['title' => 'Cacambas - Top Rio'])

@section('content')
    <h1 class="title">Cacambas</h1>
    <p class="subtitle">Cadastro e controle de status das cacambas por deposito.</p>

    <div class="card" style="margin-top: 18px;">
        <h3>Nova cacamba</h3>
        <form method="POST" action="{{ route('containers.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="identifier">Identificacao / numero</label>
                    <input id="identifier" name="identifier" required>
                </div>
                <div>
                    <label for="depot_id">Deposito</label>
                    <select id="depot_id" name="depot_id" required>
                        <option value="">Selecione</option>
                        @foreach ($depots as $depot)
                            <option value="{{ $depot->id }}">{{ $depot->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 12px;">Salvar cacamba</button>
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h3>Cacambas cadastradas</h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Identificacao</th>
                        <th>Deposito</th>
                        <th>Status</th>
                        <th>Acoes</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($containers as $container)
                    <tr>
                        <td>{{ $container->identifier }}</td>
                        <td>{{ $container->depot ? $container->depot->name : 'N/A' }}</td>
                        <td>
                            @if ($container->status === 'available')
                                <span class="badge ok">Disponivel</span>
                            @else
                                <span class="badge warning">Alocada</span>
                            @endif
                        </td>
                        <td>
                            <form class="inline" method="POST" action="{{ route('containers.destroy', $container) }}" onsubmit="return confirm('Tem certeza que deseja excluir esta cacamba?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" style="text-align: center; padding: 20px; color: var(--muted);">Nenhuma cacamba cadastrada.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
