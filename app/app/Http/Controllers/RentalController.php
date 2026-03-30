<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Container;
use App\Models\Depot;
use App\Models\Receivable;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class RentalController extends Controller
{
    public function index()
    {
        $depots = Depot::query()->orderBy('name')->get();
        $clients = Client::query()->orderBy('name')->get();

        $availableContainers = Container::query()
            ->available()
            ->with('depot')
            ->orderBy('depot_id')
            ->orderBy('identifier')
            ->get();

        $activeRentals = Rental::query()
            ->with(['container', 'depot', 'client'])
            ->where('status', Rental::STATUS_ACTIVE)
            ->orderBy('allocated_at')
            ->get();

        return view('rentals.index', compact('depots', 'clients', 'availableContainers', 'activeRentals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'exists:clients,id'],
            'street' => ['required', 'string', 'max:150'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:100'],
            'depot_id' => ['nullable', 'exists:depots,id'],
            'container_id' => ['nullable', 'exists:containers,id'],
            'value' => ['nullable', 'numeric', 'min:0'],
            'photo' => ['nullable', 'image', 'max:5120'], // 5MB max
        ]);

        try {
            DB::transaction(function () use ($data, $request): void {
                // Se container_id foi informado, usar ele (validando se está disponível)
                if (!empty($data['container_id'])) {
                    /** @var Container|null $container */
                    $container = Container::query()
                        ->available()
                        ->lockForUpdate()
                        ->find($data['container_id']);

                    if (! $container) {
                        throw new \RuntimeException('A cacamba selecionada nao esta disponivel.');
                    }
                } else {
                    // Comportamento automático: escolher primeira disponível
                    $containerQuery = Container::query()
                        ->available()
                        ->lockForUpdate()
                        ->when(
                            $data['depot_id'] ?? null,
                            fn (Builder $query, mixed $depotId) => $query->where('depot_id', $depotId)
                        )
                        ->orderBy('id');

                    /** @var Container|null $container */
                    $container = $containerQuery->first();

                    if (! $container) {
                        throw new \RuntimeException('Nao ha cacambas disponiveis no deposito selecionado.');
                    }
                }

                // Processar upload da foto se houver
                $photoPath = null;
                if ($request->hasFile('photo')) {
                    $photoPath = $request->file('photo')->store('rentals/photos', 'public');
                    
                    // Copiar para public_html/storage (já que link simbólico não funciona no servidor)
                    $sourcePath = storage_path('app/public/' . $photoPath);
                    
                    // Caminho absoluto para public_html/storage
                    // base_path() retorna: /home/.../public_html/app
                    // Precisamos: /home/.../public_html/storage
                    $publicHtmlBase = dirname(base_path()); // Já retorna até public_html
                    $publicHtmlPath = $publicHtmlBase . '/storage/' . $photoPath;
                    $destinationDir = dirname($publicHtmlPath);
                    
                    // Aguardar um pouco para garantir que o arquivo foi salvo
                    usleep(100000); // 0.1 segundo
                    
                    try {
                        // Criar diretório se não existir
                        if (!File::exists($destinationDir)) {
                            File::makeDirectory($destinationDir, 0755, true);
                        }
                        
                        // Aguardar arquivo existir (pode levar um momento)
                        $attempts = 0;
                        while (!File::exists($sourcePath) && $attempts < 10) {
                            usleep(100000); // 0.1 segundo
                            $attempts++;
                        }
                        
                        // Copiar arquivo
                        if (File::exists($sourcePath)) {
                            // Usar copy() nativo do PHP para garantir
                            $copied = @copy($sourcePath, $publicHtmlPath);
                            
                            if ($copied && File::exists($publicHtmlPath)) {
                                // Garantir permissões corretas
                                @chmod($publicHtmlPath, 0644);
                                Log::info('Imagem copiada com sucesso', [
                                    'source' => $sourcePath,
                                    'destination' => $publicHtmlPath,
                                ]);
                            } else {
                                // Tentar novamente com File::copy
                                $copied = File::copy($sourcePath, $publicHtmlPath);
                                if ($copied) {
                                    @chmod($publicHtmlPath, 0644);
                                } else {
                                    Log::warning('Falha ao copiar imagem', [
                                        'source' => $sourcePath,
                                        'destination' => $publicHtmlPath,
                                        'source_exists' => File::exists($sourcePath),
                                        'dest_dir_exists' => File::exists($destinationDir),
                                        'dest_dir_writable' => is_writable($destinationDir),
                                    ]);
                                }
                            }
                        } else {
                            Log::error('Arquivo de origem nao existe apos upload', [
                                'path' => $sourcePath,
                                'photo_path' => $photoPath,
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Erro ao copiar imagem para public_html', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'source' => $sourcePath,
                            'destination' => $publicHtmlPath,
                        ]);
                    }
                }

                $rental = Rental::query()->create([
                    'client_id' => $data['client_id'],
                    'container_id' => $container->id,
                    'depot_id' => $container->depot_id,
                    'street' => $data['street'],
                    'number' => $data['number'],
                    'complement' => $data['complement'] ?? null,
                    'photo' => $photoPath,
                    'allocated_at' => now(),
                    'status' => Rental::STATUS_ACTIVE,
                    'value' => $data['value'] ?? null,
                ]);

                $container->update(['status' => Container::STATUS_ALLOCATED]);

                // Se um valor foi informado, criar conta a receber automaticamente
                if (!empty($data['value'])) {
                    $rental->load('client');
                    Receivable::create([
                        'rental_id' => $rental->id,
                        'description' => 'Locacao de cacamba ' . $container->identifier . ' - ' . ($rental->client->name ?? 'Cliente'),
                        'value' => $data['value'],
                        'due_date' => now()->addDays(7), // Vencimento em 7 dias
                        'status' => Receivable::STATUS_PENDING,
                    ]);
                }
            });
        } catch (\RuntimeException $exception) {
            return redirect()->route('rentals.index')->withErrors([
                'rental' => $exception->getMessage(),
            ])->withInput();
        }

        return redirect()->route('rentals.index')->with('status', 'Locacao registrada com sucesso.');
    }

    public function close(Rental $rental): RedirectResponse
    {
        if ($rental->status === Rental::STATUS_CLOSED) {
            return redirect()->route('rentals.index')->withErrors([
                'rental' => 'Esta locacao ja foi encerrada.',
            ]);
        }

        DB::transaction(function () use ($rental): void {
            $rental->load('container');

            $rental->update([
                'removed_at' => now(),
                'status' => Rental::STATUS_CLOSED,
            ]);

            $rental->container->update([
                'status' => Container::STATUS_AVAILABLE,
            ]);
        });

        return redirect()->route('rentals.index')->with('status', 'Cacamba desalocada e devolvida ao deposito.');
    }

    public function toggleServiceDone(Rental $rental): RedirectResponse|JsonResponse
    {
        if ($rental->status === Rental::STATUS_CLOSED) {
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Nao e possivel marcar servico feito em locacao encerrada.'], 400);
            }
            return redirect()->route('rentals.index')->withErrors([
                'rental' => 'Nao e possivel marcar servico feito em locacao encerrada.',
            ]);
        }

        $newServiceDone = !$rental->service_done;
        
        $rental->update([
            'service_done' => $newServiceDone,
            'service_done_at' => $newServiceDone ? now() : null,
        ]);

        $message = $newServiceDone 
            ? 'Servico marcado como feito.' 
            : 'Marcacao de servico feito removida.';

        // Se for requisição AJAX, retornar JSON
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'service_done' => $newServiceDone,
                'service_done_at' => $newServiceDone ? $rental->service_done_at?->format('d/m/Y H:i') : null,
            ]);
        }

        return redirect()->route('rentals.index')->with('status', $message);
    }

    public function edit(Rental $rental): View
    {
        $rental->load(['client', 'container', 'depot']);
        return view('rentals.edit', compact('rental'));
    }

    public function update(Request $request, Rental $rental): RedirectResponse
    {
        $data = $request->validate([
            'value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $oldValue = $rental->value;
        $rental->update($data);

        // Se um valor foi definido e não havia valor antes, criar conta a receber automaticamente
        if (!empty($data['value']) && empty($oldValue) && $rental->status === Rental::STATUS_ACTIVE) {
            Receivable::create([
                'rental_id' => $rental->id,
                'description' => 'Locacao de cacamba ' . $rental->container->identifier . ' - ' . ($rental->client->name ?? 'Cliente'),
                'value' => $data['value'],
                'due_date' => now()->addDays(7), // Vencimento em 7 dias
                'status' => Receivable::STATUS_PENDING,
            ]);
        } elseif (!empty($data['value']) && !empty($oldValue) && $data['value'] != $oldValue) {
            // Se o valor foi alterado, atualizar a conta a receber pendente associada
            $receivable = Receivable::where('rental_id', $rental->id)
                ->where('status', Receivable::STATUS_PENDING)
                ->first();
            
            if ($receivable) {
                $receivable->update([
                    'value' => $data['value'],
                    'description' => 'Locacao de cacamba ' . $rental->container->identifier . ' - ' . ($rental->client->name ?? 'Cliente'),
                ]);
            } elseif ($rental->status === Rental::STATUS_ACTIVE) {
                // Se não existe e a locação está ativa, criar nova
                Receivable::create([
                    'rental_id' => $rental->id,
                    'description' => 'Locacao de cacamba ' . $rental->container->identifier . ' - ' . ($rental->client->name ?? 'Cliente'),
                    'value' => $data['value'],
                    'due_date' => now()->addDays(7),
                    'status' => Receivable::STATUS_PENDING,
                ]);
            }
        }

        return redirect()->route('rentals.index')->with('status', 'Locacao atualizada com sucesso.');
    }
}
