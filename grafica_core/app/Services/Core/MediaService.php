<?php

declare(strict_types=1);

namespace App\Services\Core;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Loja;
use App\Services\SaaS\PlanService;
use App\Services\SaaS\TenantContext;

class MediaService
{
    public function __construct(
        private readonly PlanService $planService,
        private readonly TenantContext $tenantContext,
    ) {}

    /**
     * Salva uma imagem com crop quadrado e otimização.
     * 
     * @param UploadedFile $file O arquivo enviado.
     * @param string $path O diretório de destino.
     * @param string $prefix Prefixo para o nome do arquivo.
     * @param int $quality Qualidade do JPEG (0-100).
     * @return string O caminho relativo do arquivo salvo.
     */
    public function saveWithSquareCrop(UploadedFile $file, string $path, string $prefix = 'img', int $quality = 90): string
    {
        $incomingBytes = max(0, (int) ($file->getSize() ?? 0));
        $storagePolicy = $this->planService->evaluateStoragePolicy($incomingBytes);
        if (! $storagePolicy['allowed']) {
            throw new \RuntimeException($storagePolicy['message'] ?? 'Limite de armazenamento do plano atingido. Remova arquivos ou faça upgrade.');
        }

        $source = $file->getRealPath();
        $info = @getimagesize((string) $source);

        // Fallback se não for imagem processável pelo GD
        if (!is_array($info) || !isset($info[0], $info[1], $info['mime'])) {
            $storedPath = $file->store($path, 'public');
            $this->registerStorageDeltaByPath($storedPath, +1);

            return $storedPath;
        }

        $width = (int) $info[0];
        $height = (int) $info[1];
        $side = min($width, $height);
        $srcX = (int) floor(($width - $side) / 2);
        $srcY = (int) floor(($height - $side) / 2);

        $readFunctions = [
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/jpg'  => 'imagecreatefromjpeg',
            'image/png'  => 'imagecreatefrompng',
            'image/webp' => 'imagecreatefromwebp',
        ];

        $readFunc = $readFunctions[$info['mime']] ?? null;

        if ($readFunc === null || !function_exists($readFunc) || !function_exists('imagecreatetruecolor')) {
            $storedPath = $file->store($path, 'public');
            $this->registerStorageDeltaByPath($storedPath, +1);

            return $storedPath;
        }

        $imgSrc = @$readFunc((string) $source);
        if (!$imgSrc) {
            $storedPath = $file->store($path, 'public');
            $this->registerStorageDeltaByPath($storedPath, +1);

            return $storedPath;
        }

        $imgDest = imagecreatetruecolor($side, $side);
        
        // Trata transparência para PNG
        if ($info['mime'] === 'image/png') {
            imagealphablending($imgDest, false);
            imagesavealpha($imgDest, true);
        }

        imagecopy($imgDest, $imgSrc, 0, 0, $srcX, $srcY, $side, $side);

        $filename = $prefix . '-' . Str::lower(Str::random(10)) . '.jpg';
        $relativeDir = trim($path, '/');
        Storage::disk('public')->makeDirectory($relativeDir);
        
        $relativeFilePath = $relativeDir . '/' . $filename;
        $absolutePath = storage_path('app/public/' . $relativeFilePath);

        // Salva como JPEG por padrão de leveza para SaaS
        imagejpeg($imgDest, $absolutePath, $quality);

        imagedestroy($imgSrc);
        imagedestroy($imgDest);

        $this->registerStorageDeltaByBytes(max(0, (int) @filesize($absolutePath)), +1, $relativeFilePath);

        return $relativeFilePath;
    }

    /**
     * Remove um arquivo do storage.
     */
    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            $bytes = max(0, (int) Storage::disk('public')->size($path));
            Storage::disk('public')->delete($path);
            $this->registerStorageDeltaByBytes($bytes, -1, $path);
        }
    }

    private function resolveLojaId(): ?int
    {
        return auth()->user()?->loja_id ?? $this->tenantContext->getLojaId();
    }

    private function registerStorageDeltaByPath(string $path, int $direction): void
    {
        if (! Storage::disk('public')->exists($path)) {
            return;
        }

        $bytes = max(0, (int) Storage::disk('public')->size($path));
        $this->registerStorageDeltaByBytes($bytes, $direction, $path);
    }

    private function registerStorageDeltaByBytes(int $bytes, int $direction, ?string $path = null): void
    {
        if ($bytes <= 0) {
            return;
        }

        $lojaId = $this->resolveLojaId();
        if (! $lojaId) {
            return;
        }

        if ($direction > 0) {
            Loja::query()->where('id', $lojaId)->increment('storage_used_bytes', $bytes);
        } else {
            Loja::query()->where('id', $lojaId)->update([
                'storage_used_bytes' => DB::raw('greatest(storage_used_bytes - ' . $bytes . ', 0)'),
            ]);
        }

        $this->planService->recordUsage('storage_delta', [
            'limit_key' => 'max_storage_mb',
            'delta' => $direction > 0 ? 1 : -1,
            'metadata' => [
                'bytes' => $bytes,
                'path' => $path,
                'direction' => $direction > 0 ? 'up' : 'down',
            ],
        ], $lojaId);
    }
}
