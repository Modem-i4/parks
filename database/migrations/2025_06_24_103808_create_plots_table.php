<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('plots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('park_id')->constrained('parks')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->float('area')->default(0);
            $table->json('geo_json')->default(json_encode([]));
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plots');
    }
};
