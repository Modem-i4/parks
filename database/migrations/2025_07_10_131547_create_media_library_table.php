<?php

use App\Enums\MediaType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_library', function (Blueprint $table) {
            $table->id();
            $table->string('file_path');
            $table->enum('type', MediaType::values())->default(MediaType::IMAGE->value);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('media_library');
    }
};