@extends('layouts.app', ['title' => 'Painel - Top Rio'])

@section('content')
    <h1 class="title">Painel operacional</h1>
    <p class="subtitle">Controle em tempo real de estoque, locacoes e alertas de permanencia.</p>

    <div class="grid cols-4" style="margin-top: 18px;">
        <div class="card">
            <div class="meta">Depositos</div>
            <div class="value">{{ $stats['depots'] }}</div>
        </div>
        <div class="card">
            <div class="meta">Cacambas disponiveis</div>
            <div class="value">{{ $stats['containers_available'] }}</div>
        </div>
        <div class="card">
            <div class="meta">Cacambas alocadas</div>
            <div class="value">{{ $stats['containers_allocated'] }}</div>
        </div>
        <div class="card">
            <div class="meta">Total de cacambas</div>
            <div class="value">{{ $stats['containers_total'] }}</div>
        </div>
    </div>

    <div class="grid cols-2" style="margin-top: 16px;">
        <div class="card">
            <h3>Alertas de tempo</h3>
            <p class="meta">24h = amarelo, 48h = vermelho.</p>
            <div style="display: flex; gap: 10px; margin-top: 10px;">
                <span class="badge warning">Apos 24h: {{ $stats['alerts_24h'] }}</span>
                <span class="badge danger">Apos 48h: {{ $stats['alerts_48h'] }}</span>
            </div>
        </div>
        <div class="card">
            <h3>Acao rapida</h3>
            <p class="meta">Registrar nova colocacao de cacamba.</p>
            <a href="{{ route('rentals.index') }}" class="btn btn-primary" style="display: inline-block; margin-top: 10px;">Ir para Locacao</a>
        </div>
    </div>

    <h2 style="margin-top: 24px;">Cacambas alocadas</h2>
    <div class="grid cols-3" style="margin-top: 12px;">
        @forelse ($activeRentals as $rental)
            <div class="card rental-card {{ $rental->alert_level }}">
                <div style="display: flex; justify-content: space-between; gap: 8px;">
                    <h4 style="margin: 0;">{{ $rental->container->identifier }}</h4>
                    <span class="badge {{ $rental->alert_level }}">
                        {{ $rental->elapsed_hours }}h
                    </span>
                </div>
                @if($rental->client)
                    <div class="meta"><strong>Cliente:</strong> {{ $rental->client->name }}</div>
                @endif
                <div class="meta"><strong>Endereco:</strong> {{ $rental->full_address }}</div>
                <div class="meta"><strong>Alocada em:</strong> {{ $rental->allocated_at->format('d/m/Y H:i') }}</div>
                <form class="inline" method="POST" action="{{ route('rentals.close', $rental) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger" style="margin-top: 12px;">Desalocar / Retirar</button>
                </form>
            </div>
        @empty
            <div class="card">
                <div class="meta">Nenhuma cacamba alocada no momento.</div>
            </div>
        @endforelse
    </div>
@endsection
