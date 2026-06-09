<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

final class FileUploader
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_W = 4000;
    private const MAX_H = 4000;

    /** @param array<string, mixed> $file one $_FILES entry */
    public function saveProductImage(array $file, string $destDir): array
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            throw new RuntimeException('upload_error');
        }
        $tmp = (string) $file['tmp_name'];
        if (!is_uploaded_file($tmp)) {
            throw new RuntimeException('invalid_upload');
        }
        $size = (int) ($file['size'] ?? 0);
        if ($size > self::MAX_BYTES) {
            throw new RuntimeException('file_too_large');
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmp);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($map[$mime])) {
            throw new RuntimeException('invalid_mime');
        }
        $ext = $map[$mime];
        if (function_exists('getimagesize')) {
            $info = @getimagesize($tmp);
            if ($info !== false) {
                if (($info[0] ?? 0) > self::MAX_W || ($info[1] ?? 0) > self::MAX_H) {
                    throw new RuntimeException('dimensions_too_large');
                }
            }
        }
        $ym = gmdate('Y/m');
        $dir = rtrim($destDir, '/') . '/' . $ym;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $name = bin2hex(random_bytes(16)) . '.' . $ext;
        $path = $dir . '/' . $name;
        if (!move_uploaded_file($tmp, $path)) {
            throw new RuntimeException('move_failed');
        }
        if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
            $this->stripExifJpeg($path);
        }
        $rel = 'uploads/products/' . $ym . '/' . $name;
        return ['path' => $path, 'relative' => $rel, 'mime' => $mime];
    }

    private function stripExifJpeg(string $path): void
    {
        $img = @imagecreatefromjpeg($path);
        if ($img === false) {
            return;
        }
        imagejpeg($img, $path, 90);
        imagedestroy($img);
    }
}
