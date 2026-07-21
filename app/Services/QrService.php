<?php

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use chillerlan\QRCode\Output\QROutputInterface;

class QrService
{
    private static function options(string $outputType, bool $base64 = false, int $scale = 8): QROptions
    {
        return new QROptions([
            'outputType' => $outputType,
            'eccLevel' => QRCode::ECC_M,
            'scale' => $scale,
            'imageBase64' => $base64,
            'outputBase64' => $base64,
            'imageTransparent' => false,
            'bgColor' => [255, 255, 255],
            'drawCircularModules' => false,
        ]);
    }

    public static function svg(string $data, int $size = 220): string
    {
        return (new QRCode(self::options(QROutputInterface::MARKUP_SVG, false, 5)))->render($data);
    }

    /** PNG binário — usa GD se existir; senão gera PNG P&B sem extensões. */
    public static function pngBinary(string $data): string
    {
        if (extension_loaded('gd')) {
            $options = self::options(QROutputInterface::GDIMAGE_PNG, false, 8);
            $raw = (new QRCode($options))->render($data);
            if (is_string($raw) && str_starts_with($raw, "\x89PNG")) {
                return $raw;
            }
        }
        return self::pngBinaryWithoutGd($data, 8);
    }

    public static function pngDataUri(string $data): string
    {
        return 'data:image/png;base64,' . base64_encode(self::pngBinary($data));
    }

    public static function savePng(string $data, string $path): void
    {
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        if (file_put_contents($path, self::pngBinary($data)) === false) {
            throw new RuntimeException('Não foi possível gravar o QR em ' . $path);
        }
    }

    public static function saveSvg(string $data, string $path, int $size = 220): void
    {
        file_put_contents($path, self::svg($data, $size));
    }

    /**
     * Gera PNG 1-bit a partir da matriz QR (sem GD/Imagick).
     */
    private static function pngBinaryWithoutGd(string $data, int $scale = 8): string
    {
        $options = self::options(QROutputInterface::MARKUP_SVG, false, 1);
        $qr = (new QRCode($options))->addByteSegment($data);
        $matrix = $qr->getQRMatrix();
        $size = $matrix->getSize();
        $quiet = 2;
        $modules = $size + ($quiet * 2);
        $px = $modules * $scale;

        $img = array_fill(0, $px * $px * 3, 255);
        for ($y = 0; $y < $size; $y++) {
            for ($x = 0; $x < $size; $x++) {
                if (!$matrix->check($x, $y)) {
                    continue;
                }
                $x0 = ($x + $quiet) * $scale;
                $y0 = ($y + $quiet) * $scale;
                for ($dy = 0; $dy < $scale; $dy++) {
                    for ($dx = 0; $dx < $scale; $dx++) {
                        $i = (($y0 + $dy) * $px + ($x0 + $dx)) * 3;
                        $img[$i] = 0;
                        $img[$i + 1] = 0;
                        $img[$i + 2] = 0;
                    }
                }
            }
        }

        return self::encodeTruecolorPng($px, $px, $img);
    }

    /** PNG RGB sem compressão especial (zlib). */
    private static function encodeTruecolorPng(int $w, int $h, array $rgb): string
    {
        $raw = '';
        for ($y = 0; $y < $h; $y++) {
            $raw .= "\x00"; // filter none
            $row = ($y * $w * 3);
            for ($x = 0; $x < $w * 3; $x++) {
                $raw .= chr($rgb[$row + $x]);
            }
        }
        $compressed = gzcompress($raw, 9);
        if ($compressed === false) {
            throw new RuntimeException('Falha ao comprimir PNG do QR.');
        }

        return "\x89PNG\r\n\x1a\n"
            . self::pngChunk('IHDR', pack('NNCCCCC', $w, $h, 8, 2, 0, 0, 0))
            . self::pngChunk('IDAT', $compressed)
            . self::pngChunk('IEND', '');
    }

    private static function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data)) . $type . $data . pack('N', crc32($type . $data));
    }
}
