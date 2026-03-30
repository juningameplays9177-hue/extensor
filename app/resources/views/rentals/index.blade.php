@extends('layouts.app', ['title' => 'Locacao - Top Rio'])

@section('content')
    <h1 class="title">Locacao (colocacao)</h1>
    <p class="subtitle">Registre o endereco e escolha a cacamba que sera locada.</p>

    <div class="card" style="margin-top: 18px;">
        <h3>Nova locacao</h3>
        <form method="POST" action="{{ route('rentals.store') }}" id="rentalForm" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div>
                    <label for="client_id">Cliente <span style="color: #ef4444;">*</span></label>
                    <select id="client_id" name="client_id" required>
                        <option value="">Selecione um cliente</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" @selected((string) old('client_id') === (string) $client->id)>
                                {{ $client->name }}@if($client->phone) - {{ $client->formatted_phone }}@endif
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #666; margin-top: 4px; display: block;">
                        <a href="{{ route('clients.index') }}" target="_blank" style="color: #3b82f6;">Cadastrar novo cliente</a>
                    </small>
                </div>
                <div>
                    <label for="street">Logradouro</label>
                    <input id="street" name="street" value="{{ old('street') }}" required>
                </div>
                <div>
                    <label for="number">Numero</label>
                    <input id="number" name="number" value="{{ old('number') }}" required>
                </div>
                <div>
                    <label for="complement">Complemento</label>
                    <input id="complement" name="complement" value="{{ old('complement') }}">
                </div>
                @if(auth()->user()->isAdmin())
                    <div>
                        <label for="value">Valor da Locacao (R$)</label>
                        <input type="number" id="value" name="value" step="0.01" min="0" value="{{ old('value') }}" placeholder="0.00">
                        <small style="color: #666; margin-top: 4px; display: block;">
                            Se informado, sera criada automaticamente uma conta a receber.
                        </small>
                    </div>
                @endif
                <div>
                    <label for="depot_id">Deposito de saida (opcional)</label>
                    <select id="depot_id" name="depot_id" onchange="filterContainers()">
                        <option value="">Todos os depositos</option>
                        @foreach ($depots as $depot)
                            <option value="{{ $depot->id }}" @selected((string) old('depot_id') === (string) $depot->id)>
                                {{ $depot->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="container_id">Cacamba (opcional)</label>
                    <select id="container_id" name="container_id">
                        <option value="">Auto (primeira disponivel)</option>
                        @foreach ($availableContainers as $container)
                            <option value="{{ $container->id }}" 
                                data-depot-id="{{ $container->depot_id }}"
                                @selected((string) old('container_id') === (string) $container->id)>
                                {{ $container->identifier }} - {{ $container->depot->name }}
                            </option>
                        @endforeach
                    </select>
                    <small style="color: #666; margin-top: 4px; display: block;">
                        Se nao escolher, o sistema seleciona automaticamente a primeira disponivel.
                    </small>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label for="photo">Foto da cacamba (opcional)</label>
                    <input type="file" id="photo" name="photo" accept="image/*" capture="environment">
                    <small style="color: #666; margin-top: 4px; display: block;">
                        Tire uma foto da cacamba no local para registro. Max: 5MB. Formatos: JPG, PNG, etc.
                    </small>
                    <div id="photoPreview" style="margin-top: 12px; display: none;">
                        <img id="photoPreviewImg" src="" alt="Preview" class="clickable-image" style="max-width: 300px; max-height: 200px; border-radius: 8px; border: 2px solid #e0e0e0; cursor: pointer;" onclick="if(this.src) openImageModal(this.src)">
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 12px;">Confirmar locacao</button>
        </form>
    </div>

    <script>
        function filterContainers() {
            const depotId = document.getElementById('depot_id').value;
            const containerSelect = document.getElementById('container_id');
            const options = containerSelect.querySelectorAll('option');
            
            // Primeira opção sempre visível
            options[0].style.display = '';
            
            // Filtrar outras opções
            for (let i = 1; i < options.length; i++) {
                const option = options[i];
                if (depotId === '' || option.getAttribute('data-depot-id') === depotId) {
                    option.style.display = '';
                } else {
                    option.style.display = 'none';
                }
            }
            
            // Se a opção selecionada foi ocultada, resetar para auto
            if (containerSelect.value && containerSelect.options[containerSelect.selectedIndex].style.display === 'none') {
                containerSelect.value = '';
            }
        }
        
        // Preview da foto antes de enviar
        document.getElementById('photo').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('photoPreview');
                    const previewImg = document.getElementById('photoPreviewImg');
                    preview.style.display = 'block';
                    previewImg.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('photoPreview').style.display = 'none';
            }
        });
        
        // Executar filtro ao carregar a página (caso tenha depot_id selecionado)
        document.addEventListener('DOMContentLoaded', function() {
            filterContainers();
        });
    </script>

    <h2 style="margin-top: 22px;">Painel de cacambas alocadas</h2>
    <div class="grid cols-3" style="margin-top: 12px;">
        @forelse ($activeRentals as $rental)
            <div class="card rental-card {{ $rental->service_done ? 'service-done' : $rental->alert_level }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 12px;">
                    <h4 style="margin: 0;">{{ $rental->container->identifier }}</h4>
                    <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                        @if ($rental->service_done)
                            <span class="badge" style="background: #10b981; color: white;">Servico Feito</span>
                        @elseif ($rental->alert_level === 'danger')
                            <span class="badge danger">Urgente {{ $rental->elapsed_hours }}</span>
                        @elseif ($rental->alert_level === 'warning')
                            <span class="badge warning">Atencao {{ $rental->elapsed_hours }}</span>
                        @else
                            <span class="badge ok">{{ $rental->elapsed_hours }}</span>
                        @endif
                    </div>
                </div>
                <div style="margin-bottom: 12px; padding: 12px; background: #f9fafb; border-radius: 8px; border: 2px solid #e5e7eb;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; user-select: none;">
                        <input type="checkbox" 
                               onchange="toggleServiceDone(this, {{ $rental->id }})"
                               {{ $rental->service_done ? 'checked' : '' }}
                               style="width: 20px; height: 20px; cursor: pointer;">
                        <span style="font-weight: 500; color: #374151;">Servico Feito</span>
                    </label>
                </div>
                @if($rental->photo)
                    <div style="margin: 12px 0;">
                        <img src="{{ asset('storage/' . $rental->photo) }}" 
                             alt="Foto da cacamba {{ $rental->container->identifier }}" 
                             class="clickable-image"
                             style="width: 100%; max-height: 200px; object-fit: cover; border-radius: 8px; border: 2px solid #e0e0e0;"
                             onclick="openImageModal('{{ asset('storage/' . $rental->photo) }}')"
                             title="Toque para ampliar">
                    </div>
                @endif
                <div class="meta"><strong>Cliente:</strong> {{ $rental->client ? $rental->client->name : 'N/A' }}</div>
                <div class="meta"><strong>Endereco:</strong> {{ $rental->full_address }}</div>
                <div class="meta"><strong>Deposito:</strong> {{ $rental->depot->name }}</div>
                <div class="meta"><strong>Alocada em:</strong> {{ $rental->allocated_at->format('d/m/Y H:i') }}</div>
                @if(auth()->user()->isAdmin() && $rental->value)
                    <div class="meta" style="color: #059669; font-weight: 600;">
                        <strong>Valor:</strong> R$ {{ number_format($rental->value, 2, ',', '.') }}
                    </div>
                @endif
                @if(auth()->user()->isAdmin())
                    <a href="{{ route('rentals.edit', $rental) }}" class="btn btn-muted" style="margin-top: 8px; padding: 6px 12px; font-size: 13px; display: inline-block;">Editar Valor</a>
                @endif
                @if($rental->service_done && $rental->service_done_at)
                    <div class="meta" style="color: #10b981; font-weight: 500; margin-top: 8px;">
                        <strong>✓ Servico feito em:</strong> {{ $rental->service_done_at->format('d/m/Y H:i') }}
                    </div>
                @endif
                <form class="inline" method="POST" action="{{ route('rentals.close', $rental) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn btn-danger" style="margin-top: 12px;">Desalocar / Retirar</button>
                </form>
            </div>
        @empty
            <div class="card">
                <div class="meta">Nenhuma locacao ativa no momento.</div>
            </div>
        @endforelse
    </div>

    <script>
        function toggleServiceDone(checkbox, rentalId) {
            const card = checkbox.closest('.rental-card');
            const isChecked = checkbox.checked;
            const originalState = checkbox.checked;
            
            // Atualizar visualmente imediatamente
            if (isChecked) {
                // Remover todas as classes de alerta e adicionar service-done
                card.classList.remove('normal', 'warning', 'danger');
                card.classList.add('service-done');
                
                // Atualizar badge
                const badge = card.querySelector('.badge');
                if (badge) {
                    badge.textContent = 'Servico Feito';
                    badge.className = 'badge';
                    badge.style.background = '#10b981';
                    badge.style.color = 'white';
                }
            } else {
                // Remover service-done - a página será recarregada para obter o alert_level correto
                card.classList.remove('service-done');
            }
            
            // Obter token CSRF
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                             document.querySelector('input[name="_token"]')?.value;
            
            // Fazer requisição ao servidor
            fetch(`/rentals/${rentalId}/toggle-service-done`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({})
            })
            .then(response => {
                if (response.ok) {
                    return response.json();
                }
                throw new Error('Erro na resposta');
            })
            .then(data => {
                if (data && data.success) {
                    // Se desmarcou, recarregar para obter o alert_level correto
                    if (!isChecked) {
                        window.location.reload();
                    }
                    // Se marcou, já atualizamos visualmente, então está ok
                } else {
                    // Reverter checkbox em caso de erro
                    checkbox.checked = !originalState;
                    if (!originalState) {
                        card.classList.remove('service-done');
                    }
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                // Reverter checkbox em caso de erro
                checkbox.checked = !originalState;
                if (!originalState) {
                    card.classList.remove('service-done');
                }
                // Recarregar página para garantir sincronização
                window.location.reload();
            });
        }
    </script>
@endsection
