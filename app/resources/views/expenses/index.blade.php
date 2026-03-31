@extends('layouts.app', ['title' => 'Planilha de cotação e retirada - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="title">Planilha de cotação e retirada</h1>
            <p class="subtitle">Planilha com descrição, valor, vencimento, pagamento, recibo, NF, status e ações.</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('expenses.index', array_filter(['status' => 'pending', 'date' => request('date')])) }}" class="btn btn-muted">Pendentes</a>
            <a href="{{ route('expenses.index', array_filter(['status' => 'paid', 'date' => request('date')])) }}" class="btn btn-muted">Pagas</a>
            <a href="{{ route('expenses.index', array_filter(['date' => request('date')])) }}" class="btn btn-muted">Todas</a>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">Nova entrada</a>
        </div>
    </div>

    <div class="grid cols-3" style="margin-bottom: 18px;">
        <div class="card">
            <div class="meta">Total Pendente</div>
            <div class="value" style="color: #ef4444;">R$ {{ number_format($stats['total_pending'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="meta">Total Pago</div>
            <div class="value" style="color: #10b981;">R$ {{ number_format($stats['total_paid'], 2, ',', '.') }}</div>
        </div>
        <div class="card">
            <div class="meta">Vencidas</div>
            <div class="value" style="color: #f59e0b;">{{ $stats['overdue_count'] }}</div>
        </div>
    </div>

    <div class="card" style="margin-top: 18px;">
        <h2 class="title" style="font-size: 1.1rem; margin: 0 0 12px 0;">Planilha</h2>
        <div class="table-wrap">
            <table>
                <thead style="background: #f8fafc;">
                    <tr>
                        <th>Descrição</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th>Recibo</th>
                        <th>NF</th>
                        <th>Status</th>
                        <th style="text-align: right;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($expenses as $expense)
                        <tr style="{{ $expense->isOverdue() ? 'background: #fef2f2;' : '' }}">
                            <td><strong>{{ $expense->description }}</strong></td>
                            <td><strong style="color: #ef4444;">R$ {{ number_format($expense->value, 2, ',', '.') }}</strong></td>
                            <td>{{ $expense->due_date->format('d/m/Y') }}</td>
                            <td>{{ $expense->payment_date ? $expense->payment_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $expense->receipt_number ?? '-' }}</td>
                            <td>{{ $expense->invoice_number ?? '-' }}</td>
                            <td>
                                @if($expense->status === 'paid')
                                    <span class="badge" style="background: #10b981; color: white;">Pago</span>
                                @elseif($expense->isOverdue())
                                    <span class="badge danger">Vencido</span>
                                @else
                                    <span class="badge warning">Pendente</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <a href="{{ route('expenses.edit', $expense) }}" class="btn btn-muted" style="padding: 6px 12px; font-size: 13px;">Editar</a>
                                    @if($expense->status === 'pending')
                                        <form method="POST" action="{{ route('expenses.mark-as-paid', $expense) }}" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn" style="background: #10b981; color: white; padding: 6px 12px; font-size: 13px;">Marcar Pago</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('expenses.destroy', $expense) }}" class="inline" onsubmit="return confirm('Tem certeza?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="text-align: center; padding: 20px; color: var(--muted);">
                                Nenhum registro na planilha.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
