<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('trees', function (Blueprint $table) {
            $table->foreignId('id')->constrained('green')->cascadeOnDelete();
            $table->float('height_m')->nullable();
            $table->float('trunk_circumference_cm')->nullable();
            $table->float('tilt_degree')->nullable();
            $table->float('crown_condition_percent')->nullable();
            $table->integer('area')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('trees');
    }
};
