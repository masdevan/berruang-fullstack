<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->string('type')->default('text');
            $table->string('file_path')->nullable();
            $table->string('preview_path')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();

            $table->index(['workspace_id', 'id']);
        });

        Schema::table('workspace_user', function (Blueprint $table) {
            $table->unsignedBigInteger('last_read_message_id')->nullable();
        });

        $maxIds = DB::table('workspace_messages')
            ->select('workspace_id', DB::raw('MAX(id) as max_id'))
            ->groupBy('workspace_id')
            ->get();

        foreach ($maxIds as $row) {
            DB::table('workspace_user')
                ->where('workspace_id', $row->workspace_id)
                ->update(['last_read_message_id' => $row->max_id]);
        }
    }

    public function down(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->dropColumn('last_read_message_id');
        });

        Schema::dropIfExists('workspace_messages');
    }
};
