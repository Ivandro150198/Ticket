<?php

class ImageService
{
    public static function storeUpload(array $file, string $destDir, int $maxMb = 5): ?string
    {
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || empty($file['tmp_name'])) {
            return null;
        }
        if (($file['size'] ?? 0) > $maxMb * 1024 * 1024) {
            throw new RuntimeException("Imagem demasiado grande (máx. {$maxMb}MB).");
        }

        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
        ];
        if (!isset($map[$mime])) {
            throw new RuntimeException('Formato de imagem inválido (jpg, png, webp).');
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0775, true);
        }

        $name = 'event_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $map[$mime];
        $dest = rtrim($destDir, '/\\') . DIRECTORY_SEPARATOR . $name;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new RuntimeException('Falha ao guardar imagem.');
        }

        self::createThumb($dest, $destDir . DIRECTORY_SEPARATOR . 'thumb_' . $name, 640, 400);
        return $name;
    }

    public static function createThumb(string $src, string $dest, int $w, int $h): void
    {
        $info = @getimagesize($src);
        if (!$info) {
            return;
        }
        [$sw, $sh] = $info;
        $create = match ($info['mime']) {
            'image/jpeg' => 'imagecreatefromjpeg',
            'image/png' => 'imagecreatefrompng',
            'image/webp' => function_exists('imagecreatefromwebp') ? 'imagecreatefromwebp' : null,
            default => null,
        };
        if (!$create) {
            return;
        }
        $im = $create($src);
        $dst = imagecreatetruecolor($w, $h);
        $ratio = max($w / $sw, $h / $sh);
        $nw = (int) ($sw * $ratio);
        $nh = (int) ($sh * $ratio);
        $x = (int) (($w - $nw) / 2);
        $y = (int) (($h - $nh) / 2);
        imagecopyresampled($dst, $im, $x, $y, 0, 0, $nw, $nh, $sw, $sh);
        imagejpeg($dst, $dest, 85);
        imagedestroy($im);
        imagedestroy($dst);
    }
}
