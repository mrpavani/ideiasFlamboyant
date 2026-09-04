<?php

declare(strict_types=1);

namespace Core;

/**
 * Upload de imagens com validação de tipo e tamanho.
 */
final class Upload
{
    private const ALLOWED = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];

    private const MAX_BYTES = 6_291_456; // 6 MB

    /**
     * Salva um arquivo de $_FILES e devolve o caminho relativo (dentro de /uploads).
     * @throws \RuntimeException em caso de erro de validação
     */
    public static function image(array $file, string $subdir = 'products'): string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new \RuntimeException('Falha no envio do arquivo (código ' . ($file['error'] ?? '?') . ').');
        }
        if ($file['size'] > self::MAX_BYTES) {
            throw new \RuntimeException('Imagem muito grande. Máximo de 6 MB.');
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']) ?: '';
        if (!isset(self::ALLOWED[$mime])) {
            throw new \RuntimeException('Formato inválido. Use JPG, PNG, WEBP ou GIF.');
        }

        $dir = UPLOAD_PATH . '/' . trim($subdir, '/');
        if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new \RuntimeException('Não foi possível criar a pasta de uploads.');
        }

        $name = date('Ymd-His') . '-' . bin2hex(random_bytes(4)) . '.' . self::ALLOWED[$mime];
        $target = $dir . '/' . $name;

        if (!move_uploaded_file($file['tmp_name'], $target)) {
            // fallback para servidor embutido / testes
            if (!rename($file['tmp_name'], $target)) {
                throw new \RuntimeException('Não foi possível salvar a imagem.');
            }
        }

        return trim($subdir, '/') . '/' . $name;
    }
}
