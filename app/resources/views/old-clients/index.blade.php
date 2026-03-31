@extends('layouts.app', ['title' => 'Clientes Antigos - Top Rio'])

@section('content')
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="title">Clientes Antigos</h1>
            <p class="subtitle">Lista com nome, valor a pagar e status de pagamento.</p>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('old-clients.store') }}">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="name">Cliente <span style="color: #ef4444;">*</span></label>
                    <input id="name" name="name" required value="{{ old('name') }}" placeholder="Nome do cliente">
                </div>
                <div>
                    <label for="amount_due">Valor a pagar (R$) <span style="color: #ef4444;">*</span></label>
                    <input id="amount_due" name="amount_due" type="number" step="0.01" min="0" required value="{{ old('amount_due') }}" placeholder="0,00">
                </div>
            </div>
            <div style="margin-top: 14px;">
                <button type="submit" class="btn btn-primary">Adicionar cliente</button>
            </div>
        </form>
    </div>

    <div class="card" style="margin-top: 18px;">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Valor a pagar</th>
                        <th style="text-align: center;">Pagou?</th>
                        <th style="text-align: right;">Acoes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($oldClients as $oldClient)
                        <tr>
                            <td><strong>{{ $oldClient->name }}</strong></td>
                            <td>R$ {{ number_format($oldClient->amount_due, 2, ',', '.') }}</td>
                            <td style="text-align: center;">
                                <form method="POST" action="{{ route('old-clients.toggle-checked', $oldClient) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <select name="paid_status" onchange="this.form.submit()" style="max-width: 140px; margin: 0 auto;">
                                        <option value="unpaid" {{ $oldClient->checked ? '' : 'selected' }}>Nao pagou</option>
                                        <option value="paid" {{ $oldClient->checked ? 'selected' : '' }}>Pagou</option>
                                    </select>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <form method="POST" action="{{ route('old-clients.destroy', $oldClient) }}" class="inline" onsubmit="return confirm('Tem certeza?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger" style="padding: 6px 12px; font-size: 13px;">Excluir</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 20px; color: var(--muted);">
                                Nenhum cliente antigo cadastrado.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
