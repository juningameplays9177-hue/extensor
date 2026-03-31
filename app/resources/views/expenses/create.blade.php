@extends('layouts.app', ['title' => 'Nova Conta a Pagar - Top Rio'])

@section('content')
    <h1 class="title">Nova Conta a Pagar</h1>
    <p class="subtitle">Registre uma nova despesa</p>

    <div class="card" style="margin-top: 18px;">
        <form method="POST" action="{{ route('expenses.store') }}">
            @csrf
            <div class="form-grid">
                <div style="grid-column: 1 / -1;">
                    <label for="name">Nome <span style="color: #ef4444;">*</span></label>
                    <input id="name" name="name" value="{{ old('name') }}" required autofocus placeholder="Nome do fornecedor ou referencia">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="description">Endereço <span style="color: #ef4444;">*</span></label>
                    <input id="description" name="description" value="{{ old('description') }}" required placeholder="Endereço completo">
                </div>
                <div>
                    <label for="value">Valor (R$) <span style="color: #ef4444;">*</span></label>
                    <input type="number" id="value" name="value" step="0.01" min="0" value="{{ old('value') }}" required placeholder="0.00">
                </div>
                <div>
                    <label for="due_date">Data de Vencimento <span style="color: #ef4444;">*</span></label>
                    <input type="date" id="due_date" name="due_date" value="{{ old('due_date', date('Y-m-d')) }}" required>
                </div>
                <div>
                    <label for="receipt_number">Numero do Recibo</label>
                    <input type="text" id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}" placeholder="Ex: 121">
                </div>
                <div>
                    <label for="invoice_number">Numero da NF</label>
                    <input type="text" id="invoice_number" name="invoice_number" value="{{ old('invoice_number') }}" placeholder="Ex: 342">
                </div>
                <div>
                    <label for="status">Status <span style="color: #ef4444;">*</span></label>
                    <select id="status" name="status" required>
                        <option value="pending" @selected(old('status', 'pending') === 'pending')>Pendente</option>
                        <option value="paid" @selected(old('status') === 'paid')>Pago</option>
                    </select>
                </div>
                <div id="payment_date_field" style="display: none;">
                    <label for="payment_date">Data de Pagamento</label>
                    <input type="date" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}">
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="notes">Observacoes</label>
                    <textarea id="notes" name="notes" rows="3">{{ old('notes') }}</textarea>
                </div>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 18px;">
                <button type="submit" class="btn btn-primary">Salvar Conta</button>
                <a href="{{ route('expenses.index') }}" class="btn btn-muted">Cancelar</a>
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
