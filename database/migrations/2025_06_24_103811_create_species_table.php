<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Enums\SpeciesType;

return new class extends Migration
{
    public function up()
    {
        Schema::create('species', function (Blueprint $table) {
            $table->id();
            $table->enum('type', SpeciesType::Values());
            $table->foreignId('genus_id')->constrained('genera')->cascadeOnDelete();
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
