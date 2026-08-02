<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->index(['sender_id', 'receiver_id', 'id']);
            $table->index(['receiver_id', 'sender_id', 'id']);
            $table->index(['receiver_id', 'read_at']);
            $table->dropIndex(['sender_id', 'receiver_id']);
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['sender_id', 'receiver_id', 'id']);
            $table->dropIndex(['receiver_id', 'sender_id', 'id']);
            $table->dropIndex(['receiver_id', 'read_at']);
            $table->index(['sender_id', 'receiver_id']);
        });
    }
};
