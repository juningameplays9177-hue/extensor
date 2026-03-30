@extends('layouts.app', ['title' => 'Editar Usuario - Top Rio'])

@section('content')
    <h1 class="title">Editar Usuario</h1>
    <p class="subtitle">Atualize as informacoes do usuario</p>

    <div class="card" style="margin-top: 18px;">
        <form method="POST" action="{{ route('users.update', $user) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div>
                    <label for="name">Nome</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required autofocus>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required>
                </div>
                <div>
                    <label for="role">Tipo de Usuario</label>
                    <select id="role" name="role" required>
                        <option value="colaborador" @selected(old('role', $user->role) === 'colaborador')>Colaborador</option>
                        <option value="administrador" @selected(old('role', $user->role) === 'administrador')>Administrador</option>
                    </select>
                </div>
                <div>
                    <label for="password">Nova Senha (deixe em branco para manter a atual)</label>
                    <input type="password" id="password" name="password" minlength="8">
                    <small style="color: #666; margin-top: 4px; display: block;">
                        Preencha apenas se desejar alterar a senha.
                    </small>
                </div>
                <div>
                    <label for="password_confirmation">Confirmar Nova Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" minlength="8">
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Salvar Alteracoes</button>
                <a href="{{ route('users.index') }}" class="btn btn-muted">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
