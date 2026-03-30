@extends('layouts.app', ['title' => 'Login - Top Rio'])

@section('content')
    <div style="max-width: 420px; margin: 60px auto;">
        <div class="card" style="padding: 24px;">
            <h1 class="title" style="font-size: 24px;">Top Rio</h1>
            <p class="subtitle">Acesso interno ao sistema de deposito de cacambas.</p>

            <form method="POST" action="{{ route('login.attempt') }}">
                @csrf
                <div style="margin-top: 18px;">
                    <label for="email">Usuario (email)</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus>
                </div>
                <div style="margin-top: 12px;">
                    <label for="password">Senha</label>
                    <input id="password" name="password" type="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 16px;">Entrar</button>
            </form>
        </div>
    </div>
@endsection
