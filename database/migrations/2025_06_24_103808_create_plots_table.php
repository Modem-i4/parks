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
            $table->json('coordinates')->default(json_encode([null,null]));
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('plots');
    }
};
