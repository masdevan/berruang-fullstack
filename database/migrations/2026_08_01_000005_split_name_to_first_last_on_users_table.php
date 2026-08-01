<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        DB::table('users')->select(['id', 'name'])->get()->each(function ($user) {
            $parts = preg_split('/\s+/', trim($user->name), 2);
            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $parts[0] ?? null,
                'last_name' => $parts[1] ?? null,
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id')->nullable();
        });

        DB::table('users')->select(['id', 'first_name', 'last_name'])->get()->each(function ($user) {
            DB::table('users')->where('id', $user->id)->update([
                'name' => trim(($user->first_name ?? '').' '.($user->last_name ?? '')),
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
