<?php
/**
 * Copyright (c) Since 2024 NiceShoply - All Rights Reserved
 *
 * @link       https://www.niceshoply.com
 * @author     NiceShoply <team@niceshoply.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ImageGuard\Services;

use Exception;

/**
 * 基于 GD 的图片水印与压缩服务。
 */
class ImageGuardService
{
    public static function getInstance(): static
    {
        return new static;
    }

    public function settings(): array
    {
        return [
            'text'      => (string) plugin_setting('image_guard', 'text', 'NiceShoply'),
            'position'  => (string) plugin_setting('image_guard', 'position', 'bottom-right'),
            'opacity'   => (int) plugin_setting('image_guard', 'opacity', 50),
            'font_size' => (int) plugin_setting('image_guard', 'font_size', 20),
            'font_path' => (string) plugin_setting('image_guard', 'font_path', ''),
            'color'     => (string) plugin_setting('image_guard', 'color', '#FFFFFF'),
            'quality'   => (int) plugin_setting('image_guard', 'quality', 82),
        ];
    }

    /**
     * 处理单个图片文件（覆盖写回），返回是否成功。
     *
     * @throws Exception
     */
    public function process(string $path, ?string $outPath = null): bool
    {
        if (! extension_loaded('gd')) {
            throw new Exception('PHP GD extension is required.');
        }
        if (! is_file($path)) {
            throw new Exception("File not found: {$path}");
        }

        $info = getimagesize($path);
        if ($info === false) {
            throw new Exception("Not an image: {$path}");
        }

        $mime = $info['mime'] ?? '';
        $src  = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png'  => imagecreatefrompng($path),
            'image/gif'  => imagecreatefromgif($path),
            'image/webp' => function_exists('imagecreatefromwebp') ? imagecreatefromwebp($path) : null,
            default      => null,
        };
        if (! $src) {
            throw new Exception("Unsupported image: {$mime}");
        }

        $this->drawWatermark($src);

        $outPath = $outPath ?: $path;
        $quality = max(1, min(100, $this->settings()['quality']));

        $ok = match ($mime) {
            'image/png'  => imagepng($src, $outPath, (int) round(9 - ($quality / 100 * 9))),
            'image/gif'  => imagegif($src, $outPath),
            'image/webp' => function_exists('imagewebp') ? imagewebp($src, $outPath, $quality) : imagejpeg($src, $outPath, $quality),
            default      => imagejpeg($src, $outPath, $quality),
        };

        imagedestroy($src);

        return (bool) $ok;
    }

    /**
     * 批量处理目录(storage/app/public 下的相对路径)。
     *
     * @return array{processed:int, failed:int, errors:array}
     */
    public function processDirectory(string $relativeDir): array
    {
        $base = rtrim(storage_path('app/public'), '/').'/'.ltrim($relativeDir, '/');
        $base = realpath($base) ?: $base;

        // 安全：限制在 storage/app/public 内
        $root = realpath(storage_path('app/public'));
        if ($root && strncmp($base, $root, strlen($root)) !== 0) {
            return ['processed' => 0, 'failed' => 0, 'errors' => ['path outside storage/app/public']];
        }

        $processed = 0;
        $failed    = 0;
        $errors    = [];

        $files = glob($base.'/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
        foreach ($files as $file) {
            try {
                $this->process($file);
                $processed++;
            } catch (Exception $e) {
                $failed++;
                $errors[] = basename($file).': '.$e->getMessage();
            }
        }

        return ['processed' => $processed, 'failed' => $failed, 'errors' => array_slice($errors, 0, 30)];
    }

    /**
     * 在 GD 资源上绘制文字水印。
     */
    protected function drawWatermark($img): void
    {
        $s = $this->settings();
        $text = $s['text'];
        if ($text === '') {
            return;
        }

        $w = imagesx($img);
        $h = imagesy($img);

        [$r, $g, $b] = $this->hexToRgb($s['color']);
        // GD alpha: 0 不透明, 127 全透明
        $alpha = (int) round(127 - ($s['opacity'] / 100 * 127));
        $color = imagecolorallocatealpha($img, $r, $g, $b, max(0, min(127, $alpha)));

        $margin = 12;

        if ($s['font_path'] !== '' && is_file($s['font_path']) && function_exists('imagettftext')) {
            $size = max(8, $s['font_size']);
            $box  = imagettfbbox($size, 0, $s['font_path'], $text);
            $tw   = abs($box[2] - $box[0]);
            $th   = abs($box[7] - $box[1]);
            [$x, $y] = $this->position($s['position'], $w, $h, $tw, $th, $margin, true);
            imagettftext($img, $size, 0, $x, $y, $color, $s['font_path'], $text);
        } else {
            // 内置位图字体（最大 5 号）
            $font = 5;
            $tw   = imagefontwidth($font) * strlen($text);
            $th   = imagefontheight($font);
            [$x, $y] = $this->position($s['position'], $w, $h, $tw, $th, $margin, false);
            imagestring($img, $font, $x, $y, $text, $color);
        }
    }

    protected function position(string $pos, int $w, int $h, int $tw, int $th, int $margin, bool $ttf): array
    {
        // TTF 的 y 基线在文字底部；位图字体 y 在文字顶部
        return match ($pos) {
            'top-left'    => [$margin, $ttf ? $margin + $th : $margin],
            'top-right'   => [$w - $tw - $margin, $ttf ? $margin + $th : $margin],
            'bottom-left' => [$margin, $ttf ? $h - $margin : $h - $th - $margin],
            'center'      => [(int) (($w - $tw) / 2), $ttf ? (int) (($h + $th) / 2) : (int) (($h - $th) / 2)],
            default       => [$w - $tw - $margin, $ttf ? $h - $margin : $h - $th - $margin], // bottom-right
        };
    }

    protected function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }
        if (strlen($hex) !== 6) {
            return [255, 255, 255];
        }

        return [hexdec(substr($hex, 0, 2)), hexdec(substr($hex, 2, 2)), hexdec(substr($hex, 4, 2))];
    }
}
