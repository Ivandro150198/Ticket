<?php

/** Gera ícones PWA — php database/generate_pwa_icons.php */
$dir = __DIR__ . '/../public/assets/img';
if (!is_dir($dir)) {
    mkdir($dir, 0775, true);
}

if (!extension_loaded('gd')) {
    fwrite(STDERR, "GD necessária.\n");
    exit(1);
}

function pwa_icon(int $size, string $path): void
{
    $im = imagecreatetruecolor($size, $size);
    imagesavealpha($im, true);
    $transparent = imagecolorallocatealpha($im, 0, 0, 0, 127);
    imagefill($im, 0, 0, $transparent);

    $ember = imagecolorallocate($im, 196, 92, 38);
    $emberDeep = imagecolorallocate($im, 168, 72, 28);
    $sand = imagecolorallocate($im, 244, 239, 230);
    $ink = imagecolorallocate($im, 12, 16, 22);

    $r = (int) round($size * 0.18);
    // rounded rect background
    imagefilledellipse($im, $r, $r, $r * 2, $r * 2, $ember);
    imagefilledellipse($im, $size - $r, $r, $r * 2, $r * 2, $ember);
    imagefilledellipse($im, $r, $size - $r, $r * 2, $r * 2, $ember);
    imagefilledellipse($im, $size - $r, $size - $r, $r * 2, $r * 2, $ember);
    imagefilledrectangle($im, $r, 0, $size - $r, $size, $ember);
    imagefilledrectangle($im, 0, $r, $size, $size - $r, $ember);

    // ticket shape
    $m = (int) round($size * 0.22);
    $w = $size - ($m * 2);
    $h = (int) round($size * 0.42);
    $y = (int) round(($size - $h) / 2);
    imagefilledrectangle($im, $m, $y, $m + $w, $y + $h, $sand);

    $notch = (int) round($size * 0.07);
    imagefilledellipse($im, $m + (int) ($w * 0.62), $y, $notch * 2, $notch * 2, $ember);
    imagefilledellipse($im, $m + (int) ($w * 0.62), $y + $h, $notch * 2, $notch * 2, $ember);

    // perforation
    $px = $m + (int) ($w * 0.62);
    $dash = max(2, (int) ($size * 0.02));
    for ($i = $y + $notch + 2; $i < $y + $h - $notch; $i += $dash * 2) {
        imagefilledrectangle($im, $px - 1, $i, $px + 1, min($i + $dash, $y + $h - $notch), $emberDeep);
    }

    // QR-like blocks
    $q = (int) round($size * 0.08);
    $qx = $m + (int) ($w * 0.12);
    $qy = $y + (int) ($h * 0.22);
    imagefilledrectangle($im, $qx, $qy, $qx + $q, $qy + $q, $ink);
    imagefilledrectangle($im, $qx + (int) ($q * 0.25), $qy + (int) ($q * 0.25), $qx + (int) ($q * 0.75), $qy + (int) ($q * 0.75), $sand);

    imagepng($im, $path);
    imagedestroy($im);
    echo "Wrote {$path}\n";
}

pwa_icon(180, $dir . '/icon-180.png');
pwa_icon(192, $dir . '/icon-192.png');
pwa_icon(512, $dir . '/icon-512.png');
echo "Done.\n";
