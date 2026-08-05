<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('workspace_user')
            ->join('workspaces', 'workspaces.id', '=', 'workspace_user.workspace_id')
            ->whereColumn('workspace_user.user_id', 'workspaces.owner_id')
            ->where('workspace_user.role', '!=', 'owner')
            ->update(['workspace_user.role' => 'owner']);
    }

    public function down(): void
    {
    }
};
