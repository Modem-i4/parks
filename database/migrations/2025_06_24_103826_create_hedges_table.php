<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('hedges', function (Blueprint $table) {
            $table->foreignId('id')->constrained('green')->cascadeOnDelete();
            $table->float('length_m')->nullable();
            $table->timestamps();

            $table->foreignId('hedge_row_id')->nullable()->constrained('hedge_rows')->nullOnDelete();
            $table->foreignId('hedge_shape_id')->nullable()->constrained('hedge_shapes')->nullOnDelete();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('hedges');
    }
};
