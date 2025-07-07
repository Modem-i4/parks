<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->foreignId('genus_id')->constrained('genus')->cascadeOnDelete();
            $table->string('name_ukr');
            $table->string('name_lat');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('species');
    }
};
