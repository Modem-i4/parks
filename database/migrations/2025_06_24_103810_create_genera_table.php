<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('genus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('family_id')->constrained('families')->cascadeOnDelete();
            $table->string('name_ukr');
            $table->string('name_lat');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('genus');
    }
};
