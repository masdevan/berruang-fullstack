<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->string('status')->default('member')->after('role');
            $table->foreignId('inviter_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('workspace_user', function (Blueprint $table) {
            $table->dropConstrainedForeignId('inviter_id');
            $table->dropColumn('status');
        });
    }
};
