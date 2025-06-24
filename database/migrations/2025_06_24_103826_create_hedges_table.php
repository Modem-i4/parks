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
            $table->string('hedge_type_row')->nullable();
            $table->string('hedge_type_shape')->nullable();
            $table->timestamps();

            $table->foreign('hedge_type_row')->references('name')->on('hedge_type_rows')->cascadeOnDelete();
            $table->foreign('hedge_type_shape')->references('name')->on('hedge_type_shapes')->cascadeOnDelete();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('hedges');
    }
};
