@extends('layouts.app', ['title' => 'Novo Usuario - Top Rio'])

@section('content')
    <h1 class="title">Novo Usuario</h1>
    <p class="subtitle">Cadastre um novo usuario no sistema</p>

    <div class="card" style="margin-top: 18px;">
        <form method="POST" action="{{ route('users.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Nome</label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <label for="role">Tipo de Usuario</label>
                    <select id="role" name="role" required>
                        <option value="colaborador" @selected(old('role') === 'colaborador')>Colaborador</option>
                        <option value="administrador" @selected(old('role') === 'administrador')>Administrador</option>
                    </select>
                </div>
                <div>
                    <label for="password">Senha</label>
                    <input type="password" id="password" name="password" required minlength="8">
                </div>
                <div>
                    <label for="password_confirmation">Confirmar Senha</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required minlength="8">
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Criar Usuario</button>
                <a href="{{ route('users.index') }}" class="btn btn-muted">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
