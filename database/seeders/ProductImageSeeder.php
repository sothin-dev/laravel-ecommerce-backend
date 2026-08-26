<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Generates real local placeholder images (PNG) for every product & category
 * and attaches them via the standard storage disk, exactly like uploads would be.
 */
class ProductImageSeeder extends Seeder
{
    private const PALETTES = [
        ['#4f46e5', '#7c3aed'],
        ['#0ea5e9', '#2563eb'],
        ['#10b981', '#059669'],
        ['#f59e0b', '#ea580c'],
        ['#ef4444', '#be123c'],
        ['#8b5cf6', '#d946ef'],
        ['#14b8a6', '#0891b2'],
        ['#f97316', '#dc2626'],
        ['#6366f1', '#3b82f6'],
        ['#84cc16', '#16a34a'],
    ];

    public function run(): void
    {
        if (! function_exists('imagecreatetruecolor')) {
            $this->command->error('PHP GD extension is required for this seeder.');
            return;
        }

        $font = 'C:\\Windows\\Fonts\\arialbd.ttf';

        // ── Products ──
        foreach (Product::all() as $index => $product) {
            $path      = "products/product-{$product->id}.png";
            $palette   = self::PALETTES[$index % count(self::PALETTES)];

            $this->generateImage($path, $product->name, $palette[0], $palette[1], $font);
            $product->update(['image' => $path]);

            // Gallery: two extra variants with shifted hues
            ProductImage::where('product_id', $product->id)->delete();
            $galleryPalette = self::PALETTES[($index + 3) % count(self::PALETTES)];
            $altPath        = "products/product-{$product->id}-alt.png";
            $this->generateImage($altPath, $product->name, $galleryPalette[1], $galleryPalette[0], $font);

            ProductImage::create([
                'product_id' => $product->id,
                'image_path' => $altPath,
                'alt_text'   => $product->name,
                'sort_order' => 1,
            ]);
        }

        // ── Categories ──
        foreach (Category::all() as $index => $category) {
            $path    = "categories/category-{$category->id}.png";
            $palette = self::PALETTES[($index + 5) % count(self::PALETTES)];
            $this->generateImage($path, $category->name, $palette[0], $palette[1], $font);
            $category->update(['image' => $path]);
        }

        $this->command->info('Generated images for ' . Product::count() . ' products and ' . Category::count() . ' categories.');
    }

    /**
     * Draw a 600x600 gradient tile with the item name wrapped in the center.
     */
    private function generateImage(string $path, string $name, string $hexFrom, string $hexTo, string $font): void
    {
        $size = 600;
        $img  = imagecreatetruecolor($size, $size);

        [$r1, $g1, $b1] = $this->hexToRgb($hexFrom);
        [$r2, $g2, $b2] = $this->hexToRgb($hexTo);

        // Vertical gradient
        for ($y = 0; $y < $size; $y++) {
            $t     = $y / $size;
            $color = imagecolorallocate(
                $img,
                (int) round($r1 + ($r2 - $r1) * $t),
                (int) round($g1 + ($g2 - $g1) * $t),
                (int) round($b1 + ($b2 - $b1) * $t)
            );
            imageline($img, 0, $y, $size, $y, $color);
        }

        // Decorative translucent circles
        $white = imagecolorallocatealpha($img, 255, 255, 255, 100);
        imagefilledellipse($img, 520, 90, 260, 260, $white);
        imagefilledellipse($img, 70, 540, 200, 200, $white);

        // Wrapped name text
        $lines    = $this->wrapText($name, 18);
        $fontSize = 30;
        $lineH    = 44;
        $startY   = (int) (($size - (count($lines) - 1) * $lineH) / 2);

        $textColor = imagecolorallocate($img, 255, 255, 255);

        if (is_file($font)) {
            foreach ($lines as $i => $line) {
                $box   = imagettfbbox($fontSize, 0, $font, $line);
                $x     = (int) (($size - ($box[2] - $box[0])) / 2);
                $y     = $startY + $i * $lineH;
                imagettftext($img, $fontSize, 0, $x, $y, $textColor, $font, $line);
            }
        } else {
            // Fallback to built-in font
            foreach ($lines as $i => $line) {
                $w = imagefontwidth(5) * strlen($line);
                imagestring($img, 5, (int) max(5, ($size - $w) / 2), $startY + $i * 20, $line, $textColor);
            }
        }

        ob_start();
        imagepng($img, null, 6);
        $binary = (string) ob_get_clean();
        imagedestroy($img);

        Storage::disk('public')->put($path, $binary);
    }

    private function wrapText(string $text, int $maxChars): array
    {
        $words = preg_split('/\s+/', trim($text));
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            $candidate = $current === '' ? $word : "{$current} {$word}";
            if (mb_strlen($candidate) <= $maxChars) {
                $current = $candidate;
            } else {
                if ($current !== '') $lines[] = $current;
                $current = $word;
            }
        }
        if ($current !== '') $lines[] = $current;

        return array_slice($lines, 0, 4); // max 4 lines
    }

    private function hexToRgb(string $hex): array
    {
        $hex = ltrim($hex, '#');
        return [
            hexdec(substr($hex, 0, 2)),
            hexdec(substr($hex, 2, 2)),
            hexdec(substr($hex, 4, 2)),
        ];
    }
}
