<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\TagType;

return new class extends Migration
{
    public function up()
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('public')->default(false);
            $table->boolean('custom')->default(true);
            $table->enum('type', TagType::Values());
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
