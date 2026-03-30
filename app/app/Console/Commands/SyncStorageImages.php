<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class SyncStorageImages extends Command
{
    protected $signature = 'storage:sync-images';
    protected $description = 'Sincroniza todas as imagens do storage para public_html/storage';

    public function handle(): int
    {
        $sourceDir = storage_path('app/public/rentals/photos');
        $destinationDir = dirname(base_path()) . '/public_html/storage/rentals/photos';

        if (!File::exists($sourceDir)) {
            $this->error('Diretorio de origem nao existe: ' . $sourceDir);
            return 1;
        }

        if (!File::exists($destinationDir)) {
            File::makeDirectory($destinationDir, 0755, true);
            $this->info('Diretorio de destino criado: ' . $destinationDir);
        }

        $files = File::files($sourceDir);
        $copied = 0;
        $skipped = 0;

        foreach ($files as $file) {
            $filename = $file->getFilename();
            $destinationPath = $destinationDir . '/' . $filename;

            if (!File::exists($destinationPath)) {
                File::copy($file->getPathname(), $destinationPath);
                chmod($destinationPath, 0644);
                $copied++;
                $this->info("Copiado: {$filename}");
            } else {
                $skipped++;
            }
        }

        $this->info("Sincronizacao concluida! Copiados: {$copied}, Ignorados: {$skipped}");
        return 0;
    }
}
