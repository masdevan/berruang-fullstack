<?php

use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        Message::whereNull('preview_path')
            ->whereNotNull('file_path')
            ->get()
            ->each(function (Message $m) {
                $preview = pathinfo($m->file_path, PATHINFO_DIRNAME).'/'
                    .pathinfo($m->file_path, PATHINFO_FILENAME).'.preview.'
                    .(in_array(strtolower(pathinfo($m->file_path, PATHINFO_EXTENSION)), ['jpg', 'jpeg']) ? 'jpg' : 'webp');

                if (Storage::disk('public')->exists($preview)) {
                    $m->update(['preview_path' => $preview]);
                }
            });

        User::whereNull('avatar_preview_path')
            ->whereNotNull('avatar')
            ->get()
            ->each(function (User $u) {
                $preview = pathinfo($u->avatar, PATHINFO_DIRNAME).'/'
                    .pathinfo($u->avatar, PATHINFO_FILENAME).'.preview.webp';

                if (Storage::disk('public')->exists($preview)) {
                    $u->update(['avatar_preview_path' => $preview]);
                }
            });
    }

    public function down(): void
    {
    }
};
