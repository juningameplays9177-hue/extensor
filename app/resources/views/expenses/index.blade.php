@extends('layouts.app', ['title' => 'Contas a Pagar - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="title">Contas a Pagar</h1>
            <p class="subtitle">Gerencie as despesas do dia</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <a href="{{ route('expenses.index', array_filter(['status' => 'pending', 'date' => request('date'), 'saldo_date' => request('saldo_date')])) }}" class="btn btn-muted">Pendentes</a>
            <a href="{{ route('expenses.index', array_filter(['status' => 'paid', 'date' => request('date'), 'saldo_date' => request('saldo_date')])) }}" class="btn btn-muted">Pagas</a>
            <a href="{{ route('expenses.index', array_filter(['date' => request('date'), 'saldo_date' => request('saldo_date')])) }}" class="btn btn-muted">Todas</a>
            <a href="{{ route('expenses.create') }}" class="btn btn-primary">Nova Conta</a>
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
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Descricao</th>
                        <th>Valor</th>
                        <th>Vencimento</th>
                        <th>Pagamento</th>
                        <th>Recibo</th>
                        <th>NF</th>
                        <th>Status</th>
                        <th style="text-align: right;">Acoes</th>
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
                                Nenhuma conta a pagar cadastrada.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card" style="margin-top: 24px;">
        <div style="display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; margin-bottom: 16px;">
            <div>
                <h2 class="title" style="font-size: 1.15rem; margin: 0 0 6px 0;">Declaração de saída de gastos do dia</h2>
                <p class="subtitle" style="margin: 0;">Lista do dia com nome e valor.</p>
            </div>
            <form method="get" action="{{ route('expenses.index') }}" style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                @if(request()->has('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request()->has('date'))
                    <input type="hidden" name="date" value="{{ request('date') }}">
                @endif
                <label for="saldo_date_pick" style="font-size: 14px; color: var(--muted);">Data</label>
                <input type="date" id="saldo_date_pick" name="saldo_date" value="{{ $saldoDate }}" style="padding: 8px 12px; border: 1px solid var(--border); border-radius: 8px; font: inherit;" onchange="this.form.submit()">
            </form>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th style="width: 48px;">#</th>
                        <th>Nome</th>
                        <th style="width: 140px;">Valor (R$)</th>
                        <th style="text-align: right; width: 200px;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @if($saldoGastosItems->isEmpty())
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 16px; color: var(--muted);">Nenhum lancamento para esta data.</td>
                        </tr>
                    @endif
                    @foreach ($saldoGastosItems as $i => $item)
                        @php $sid = 'saldo-upd-' . $item->id; @endphp
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td style="vertical-align: middle;">
                                <form id="{{ $sid }}" method="POST" action="{{ route('expenses.saldo-gastos.update', $item) }}" style="display: none;" aria-hidden="true">
                                    @csrf
                                    @method('PUT')
                                </form>
                                @if(request()->has('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}" form="{{ $sid }}">
                                @endif
                                @if(request()->has('date'))
                                    <input type="hidden" name="date" value="{{ request('date') }}" form="{{ $sid }}">
                                @endif
                                <input type="hidden" name="saldo_date" value="{{ $saldoDate }}" form="{{ $sid }}">
                                <input type="text" name="name" form="{{ $sid }}" value="{{ old('name', $item->name) }}" required
                                    style="width: 100%; max-width: 420px; padding: 8px 10px; border: 1px solid var(--border); border-radius: 8px; font: inherit;" placeholder="Nome">
                            </td>
                            <td style="vertical-align: middle;">
                                <input type="number" name="value" form="{{ $sid }}" step="0.01" min="0" value="{{ old('value', $item->value) }}"
                                    style="width: 100%; padding: 8px 10px; border: 1px solid var(--border); border-radius: 8px; font: inherit;" placeholder="0,00">
                            </td>
                            <td style="text-align: right; vertical-align: middle;">
                                <div style="display: flex; gap: 8px; justify-content: flex-end; flex-wrap: wrap;">
                                    <button type="submit" form="{{ $sid }}" class="btn btn-muted" style="padding: 6px 12px; font-size: 13px;">Salvar</button>
                                    <form method="POST" action="{{ route('expenses.saldo-gastos.destroy', $item) }}" onsubmit="return confirm('Remover este lancamento?');" style="display: inline;">
                                        @csrf
                                        @method('DELETE')
                                        @if(request()->has('status'))
                                            <input type="hidden" name="status" value="{{ request('status') }}">
                                        @endif
                                        @if(request()->has('date'))
                                            <input type="hidden" name="date" value="{{ request('date') }}">
                                        @endif
                                        <input type="hidden" name="saldo_date" value="{{ $saldoDate }}">
                                        <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr>
                        <td colspan="4" style="padding: 12px; background: var(--ok); font-weight: 600;">
                            Total do dia: R$ {{ number_format($saldoGastosTotal, 2, ',', '.') }}
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" style="padding: 12px;">
                            <form method="POST" action="{{ route('expenses.saldo-gastos.store') }}" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: center;">
                                @csrf
                                <input type="hidden" name="ref_date" value="{{ $saldoDate }}">
                                @if(request()->has('status'))
                                    <input type="hidden" name="status" value="{{ request('status') }}">
                                @endif
                                @if(request()->has('date'))
                                    <input type="hidden" name="date" value="{{ request('date') }}">
                                @endif
                                <input type="hidden" name="saldo_date" value="{{ $saldoDate }}">
                                <span style="font-size: 13px; color: var(--muted);">Novo:</span>
                                <input type="text" name="name" required placeholder="Nome"
                                    style="flex: 1; min-width: 160px; padding: 8px 10px; border: 1px solid var(--border); border-radius: 8px; font: inherit;">
                                <input type="number" name="value" step="0.01" min="0" placeholder="Valor"
                                    style="width: 120px; padding: 8px 10px; border: 1px solid var(--border); border-radius: 8px; font: inherit;">
                                <button type="submit" class="btn btn-primary" style="padding: 8px 14px; font-size: 13px;">Adicionar</button>
                            </form>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
