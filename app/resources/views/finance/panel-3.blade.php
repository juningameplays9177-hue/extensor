@extends('layouts.app', ['title' => 'Painel 3 - Financeiro - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="title">Painel 3</h1>
            <p class="subtitle">Caixa, entradas e saídas no dia a dia, e pessoas que me devem.</p>
        </div>
    </div>

    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card">
            <div class="meta">Dinheiro em caixa</div>
            <div class="value" style="color: {{ $cashBalance >= 0 ? '#10b981' : '#ef4444' }};">
                R$ {{ number_format($cashBalance, 2, ',', '.') }}
            </div>
        </div>
        <div class="card">
            <div class="meta">Total de entradas (recebidas)</div>
            <div class="value" style="color: #2563eb;">R$ {{ number_format($totalIn, 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="meta">Total de saídas (pagas)</div>
            <div class="value" style="color: #ef4444;">R$ {{ number_format($totalOut, 2, ',', '.') }}</div>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        <h3 style="margin-bottom: 4px;">Entradas x Saídas (últimos 14 dias)</h3>
        <p class="meta">Valores por dia com base em data de pagamento/recebimento.</p>
        <div style="margin-top: 14px;">
            <canvas id="cashFlowChart" height="110"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const labels = @json($labels);
        const entradas = @json($dailyIn);
        const saidas = @json($dailyOut);

        const ctx = document.getElementById('cashFlowChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels,
                    datasets: [
                        {
                            label: 'Entradas',
                            data: entradas,
                            borderColor: '#2563eb',
                            backgroundColor: 'rgba(37, 99, 235, 0.12)',
                            tension: 0.3,
                            fill: true
                        },
                        {
                            label: 'Saídas',
                            data: saidas,
                            borderColor: '#ef4444',
                            backgroundColor: 'rgba(239, 68, 68, 0.12)',
                            tension: 0.3,
                            fill: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return 'R$ ' + Number(value).toLocaleString('pt-BR');
                                }
                            }
                        }
                    }
                }
            });
        }
    </script>
@endsection
