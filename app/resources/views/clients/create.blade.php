@extends('layouts.app', ['title' => 'Novo Cliente - Top Rio'])

@section('content')
    <h1 class="title">Novo Cliente</h1>
    <p class="subtitle">Cadastre um novo cliente no sistema</p>

    <div class="card" style="margin-top: 18px;">
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Nome / Razao Social <span style="color: #ef4444;">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus>
                </div>
                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}">
                </div>
                <div>
                    <label for="phone">Telefone</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="(00) 00000-0000">
                </div>
                <div>
                    <label for="document">CPF / CNPJ</label>
                    <input type="text" id="document" name="document" value="{{ old('document') }}" placeholder="000.000.000-00">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="address">Endereco</label>
                    <textarea id="address" name="address" rows="3">{{ old('address') }}</textarea>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Criar Cliente</button>
                <a href="{{ route('clients.index') }}" class="btn btn-muted">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
