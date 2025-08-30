<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('parks', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique()->nullable();
            $table->string('address');
            $table->float('area')->default(0);
            $table->text('description')->nullable();
            $table->string('operator')->nullable();
            $table->json('geo_json')->default(json_encode([]));
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('parks');
    }
};
