<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('markers_tags', function (Blueprint $table) {
            $table->foreignId('marker_id')->constrained('markers')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['marker_id', 'tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('markers_tags');
    }
};
