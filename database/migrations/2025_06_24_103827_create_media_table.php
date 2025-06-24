<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\MediaType;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('mediaable_type');
            $table->unsignedBigInteger('mediaable_id');
            $table->string('file_path');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->enum('type', MediaType::values())->default(MediaType::IMAGE->value);
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
