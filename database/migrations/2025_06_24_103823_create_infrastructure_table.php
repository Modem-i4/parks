<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('infrastructure', function (Blueprint $table) {
            $table->foreignId('id')->constrained('markers')->cascadeOnDelete();
            $table->string('name');
            $table->foreignId('infrastructure_type_id')->nullable()->constrained('infrastructure_type')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('infrastructure');
    }
};
