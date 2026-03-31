@extends('layouts.app', ['title' => 'Contas a Receber - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="title">Contas a Receber</h1>
            <p class="subtitle">Gerencie os recebimentos</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('receivables.create') }}" class="btn btn-primary">Nova Conta</a>
        </div>
    </div>

    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card">
            <div class="meta">Total Pendente</div>
            <div class="value" style="color: #3b82f6;">R$ {{ number_format($stats['total_pending'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="meta">Total Recebido</div>
            <div class="value" style="color: #10b981;">R$ {{ number_format($stats['total_paid'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="meta">Vencidas</div>
            <div class="value" style="color: #f59e0b;">{{ $stats['overdue_count'] }}</div>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Endereco</th>
                        <th>Valor devido</th>
                        <th>Recibo</th>
                        <th>NF</th>
                        <th style="text-align: right;">Acoes</th>
                        <th style="width: 44px; text-align: center;">Ok</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($receivables as $receivable)
                        <tr style="{{ $receivable->isOverdue() ? 'background: #fef2f2;' : '' }}">
                            <td><strong>{{ $receivable->rental ? $receivable->rental->client->name : ($receivable->description ?? '-') }}</strong></td>
                            <td>{{ $receivable->rental ? $receivable->rental->full_address : '-' }}</td>
                            <td><strong style="color: #3b82f6;">R$ {{ number_format($receivable->value, 2, ',', '.') }}</strong></td>
                            <td>{{ $receivable->receipt_number ?? '-' }}</td>
                            <td>{{ $receivable->invoice_number ?? '-' }}</td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('receivables.edit', $receivable) }}" class="btn btn-muted" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                    @if($receivable->status === 'pending')
                                        <form method="POST" action="{{ route('receivables.mark-as-paid', $receivable) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn" style="background: #10b981; color: white; padding: 6px 12px; font-size: 13px;">Marcar Recebido</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('receivables.destroy', $receivable) }}" class="inline" onsubmit="return confirm('Tem certeza?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                                    </form>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                @if($receivable->status === 'paid')
                                    <input type="checkbox" checked disabled aria-label="Recebido">
                                @else
                                    <form method="POST" action="{{ route('receivables.mark-as-paid', $receivable) }}" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="checkbox"
                                               onchange="this.form.submit()"
                                               aria-label="Marcar como recebido">
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 20px; color: var(--muted);">
                                Nenhuma conta a receber cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
