@extends('layouts.app', ['title' => 'Usuarios - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
        <div>
            <h1 class="title">Gerenciamento de Usuarios</h1>
            <p class="subtitle">Gerencie usuarios do sistema (apenas administradores)</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">Novo Usuario</a>
    </div>

    <div class="card" style="margin-top: 18px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Email</th>
                        <th>Tipo</th>
                        <th>Criado em</th>
                        <th style="text-align: right;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td><strong>{{ $user->name }}</strong></td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role === 'administrador')
                                    <span class="badge" style="background: #dbeafe; color: #1e40af;">Administrador</span>
                                @else
                                    <span class="badge" style="background: #e2e8f0; color: #334155;">Colaborador</span>
                                @endif
                            </td>
                            <td>{{ $user->created_at->format('d/m/Y') }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('users.edit', $user) }}" class="btn btn-muted" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                    @if($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('users.destroy', $user) }}" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este usuario?');">
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
                            <td colspan="5" style="text-align: center; padding: 20px; color: var(--muted);">
                                Nenhum usuario cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
