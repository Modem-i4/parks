<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('bushes', function (Blueprint $table) {
            $table->foreignId('id')->constrained('green')->cascadeOnDelete();
            $table->integer('quantity')->nullable();
            $table->integer('area')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('bushes');
    }
};
