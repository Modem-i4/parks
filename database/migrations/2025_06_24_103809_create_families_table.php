<?php

use App\Enums\GreenType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('families', function (Blueprint $table) {
            $table->id();
            $table->string('name_ukr');
            $table->string('name_lat');
            $table->enum('type', GreenType::Values());
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('families');
    }
};
