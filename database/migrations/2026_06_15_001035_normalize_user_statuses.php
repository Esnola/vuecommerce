<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->whereNull('status')
            ->orWhereNotIn('status', ['active', 'pending', 'suspend'])
            ->update(['status' => 'pending']);
    }

    public function down(): void
    {
        //
    }
};
