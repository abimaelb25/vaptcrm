<?php

declare(strict_types=1);

namespace App\Services\Core;

/*
| Autoria: Abimael Borges
| Site: https://abimaelborges.adv.br
| Data: 2026-04-10
*/

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MediaService
{
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
        $source = $file->getRealPath();
        $info = @getimagesize((string) $source);

        // Fallback se não for imagem processável pelo GD
        if (!is_array($info) || !isset($info[0], $info[1], $info['mime'])) {
            return $file->store($path, 'public');
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
            return $file->store($path, 'public');
        }

        $imgSrc = @$readFunc((string) $source);
        if (!$imgSrc) {
            return $file->store($path, 'public');
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

        return $relativeFilePath;
    }

    /**
     * Remove um arquivo do storage.
     */
    public function delete(string $path): void
    {
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
