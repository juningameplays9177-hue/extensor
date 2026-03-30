@extends('layouts.app', ['title' => 'Editar Locacao - Top Rio'])

@section('content')
    <h1 class="title">Editar Locacao</h1>
    <p class="subtitle">Atualize o valor da locacao</p>

    <div class="card" style="margin-top: 18px;">
        <div style="margin-bottom: 18px; padding: 12px; background: #f9fafb; border-radius: 8px;">
            <div class="meta"><strong>Cacamba:</strong> {{ $rental->container->identifier }}</div>
            <div class="meta"><strong>Cliente:</strong> {{ $rental->client->name ?? 'N/A' }}</div>
            <div class="meta"><strong>Endereco:</strong> {{ $rental->full_address }}</div>
            <div class="meta"><strong>Alocada em:</strong> {{ $rental->allocated_at->format('d/m/Y H:i') }}</div>
        </div>

        <form method="POST" action="{{ route('rentals.update', $rental) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div>
                    <label for="value">Valor da Locacao (R$)</label>
                    <input type="number" 
                           id="value" 
                           name="value" 
                           step="0.01" 
                           min="0"
                           value="{{ old('value', $rental->value) }}"
                           placeholder="0.00">
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Salvar Valor</button>
                <a href="{{ route('rentals.index') }}" class="btn btn-muted">Cancelar</a>
            </div>
        </form>
    </div>
@endsection
