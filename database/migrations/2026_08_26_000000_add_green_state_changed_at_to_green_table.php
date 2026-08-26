<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('green', function (Blueprint $table) {
            $table->date('green_state_changed_at')->nullable()->after('green_state');
        });

        DB::table('green')
            ->whereNotNull('updated_at')
            ->update(['green_state_changed_at' => DB::raw('DATE(updated_at)')]);
    }

    public function down(): void
    {
        Schema::table('green', function (Blueprint $table) {
            $table->dropColumn('green_state_changed_at');
        });
    }
};
