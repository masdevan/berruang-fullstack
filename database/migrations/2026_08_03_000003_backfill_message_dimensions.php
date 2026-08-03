<?php

use App\Models\Message;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        Message::whereNotNull('file_path')
            ->whereNull('width')
            ->get()
            ->each(function (Message $m) {
                $path = storage_path('app/public/'.$m->file_path);
                if (! is_file($path)) {
                    return;
                }

                $size = @getimagesize($path);
                if (is_array($size)) {
                    $m->update(['width' => $size[0], 'height' => $size[1]]);

                    return;
                }

                if (! str_ends_with(strtolower($m->file_path), '.svg')) {
                    return;
                }

                $svg = @file_get_contents($path);
                if ($svg === false) {
                    return;
                }

                preg_match('/<svg[^>]*?width\s*=\s*["\']([\d.]+)["\']/', $svg, $w);
                preg_match('/<svg[^>]*?height\s*=\s*["\']([\d.]+)["\']/', $svg, $h);

                if (isset($w[1], $h[1])) {
                    $m->update(['width' => (int) $w[1], 'height' => (int) $h[1]]);
                } elseif (preg_match('/viewBox\s*=\s*["\']\s*[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s*["\']/', $svg, $vb)) {
                    $m->update(['width' => (int) $vb[1], 'height' => (int) $vb[2]]);
                }
            });
    }

    public function down(): void
    {
    }
};
