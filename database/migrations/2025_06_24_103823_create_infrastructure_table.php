<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('infrastructure', function (Blueprint $table) {
            $table->foreignId('id')->constrained('markers')->cascadeOnDelete();
            $table->string('name');
            $table->string('icon')->default('default_icon.png');
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('infrastructure');
    }
};
