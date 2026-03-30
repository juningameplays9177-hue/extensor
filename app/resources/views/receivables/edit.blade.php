@extends('layouts.app', ['title' => 'Editar Conta a Receber - Top Rio'])

@section('content')
    <h1 class="title">Editar Conta a Receber</h1>
    <p class="subtitle">Atualize as informacoes do recebimento</p>

    <div class="card" style="margin-top: 18px;">
        <form method="POST" action="{{ route('receivables.update', $receivable) }}">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div>
                    <label for="rental_id">Locacao (opcional)</label>
                    <select id="rental_id" name="rental_id">
                        <option value="">Nenhuma</option>
                        @foreach ($rentals as $rental)
                            <option value="{{ $rental->id }}" @selected(old('rental_id', $receivable->rental_id) == $rental->id)>
                                {{ $rental->container->identifier }} - {{ $rental->client->name ?? 'N/A' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="description">Descricao <span style="color: #ef4444;">*</span></label>
                    <input id="description" name="description" value="{{ old('description', $receivable->description) }}" required autofocus>
                </div>
                <div>
                    <label for="value">Valor (R$) <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="value" name="value" step="0.01" min="0" value="{{ old('value', $receivable->value) }}" required>
                </div>
                <div>
                    <label for="due_date">Data de Vencimento <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date', $receivable->due_date->format('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="receipt_number">Numero do Recibo</label>
                    <input type="text" id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $receivable->receipt_number) }}">
                </div>
                <div>
                    <label for="invoice_number">Numero da NF</label>
                    <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number', $receivable->invoice_number) }}">
                </div>
                <div>
                    <label for="status">Status <span style="color: #ef4444;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="pending" @selected(old('status', $receivable->status) === 'pending')>Pendente</option>
                        <option value="paid" @selected(old('status', $receivable->status) === 'paid')>Recebido</option>
                    </select>
                </div>
                <div id="payment_date_field">
                    <label for="payment_date">Data de Recebimento</label>
                    <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', $receivable->payment_date ? $receivable->payment_date->format('Y-m-d') : '') }}">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="notes">Observacoes</label>
                    <textarea id="notes" name="notes" rows="3">{{ old('notes', $receivable->notes) }}</textarea>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Salvar Alteracoes</button>
                <a href="{{ route('receivables.index') }}" class="btn btn-muted">Cancelar</a>
            </div>
        </form>
    </div>

    <script>
        document.getElementById('status').addEventListener('change', function() {
            const paymentDateField = document.getElementById('payment_date_field');
            if (this.value === 'paid') {
                paymentDateField.style.display = 'block';
            } else {
                paymentDateField.style.display = 'none';
            }
        });
        
        // Trigger on load
        document.getElementById('status').dispatchEvent(new Event('change'));
    </script>
@endsection
