<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('green', function (Blueprint $table) {
            $table->string('inventory_number_old')
                ->nullable()
                ->after('inventory_number');
        });
    }

    public function down(): void
    {
        Schema::table('green', function (Blueprint $table) {
            $table->dropColumn('inventory_number_old');
        });
    }
};
