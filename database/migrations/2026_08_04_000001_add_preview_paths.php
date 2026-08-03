<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->string('preview_path')->nullable()->after('file_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('avatar_preview_path')->nullable()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('preview_path');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('avatar_preview_path');
        });
    }
};
