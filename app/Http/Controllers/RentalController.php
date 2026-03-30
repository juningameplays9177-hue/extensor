<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Models\Depot;
use App\Models\Rental;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class RentalController extends Controller
{
    public function index()
    {
        $depots = Depot::query()->orderBy('name')->get();

        $availableContainers = Container::query()
            ->available()
            ->with('depot')
            ->orderBy('depot_id')
            ->orderBy('identifier')
            ->get();

        $activeRentals = Rental::query()
            ->with(['container', 'depot'])
            ->where('status', Rental::STATUS_ACTIVE)
            ->orderBy('allocated_at')
            ->get();

        return view('rentals.index', compact('depots', 'availableContainers', 'activeRentals'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'street' => ['required', 'string', 'max:150'],
            'number' => ['required', 'string', 'max:30'],
            'complement' => ['nullable', 'string', 'max:100'],
            'depot_id' => ['nullable', 'exists:depots,id'],
            'container_id' => ['nullable', 'exists:containers,id'],
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

                Rental::query()->create([
                    'container_id' => $container->id,
                    'depot_id' => $container->depot_id,
                    'street' => $data['street'],
                    'number' => $data['number'],
                    'complement' => $data['complement'] ?? null,
                    'photo' => $photoPath,
                    'allocated_at' => now(),
                    'status' => Rental::STATUS_ACTIVE,
                ]);

                $container->update(['status' => Container::STATUS_ALLOCATED]);
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
}
