<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('green_work_recommendations', function (Blueprint $table) {
            $table->foreignId('green_work_id')->constrained('green_works_history')->cascadeOnDelete();
            $table->foreignId('recommendation_id')->constrained('recommendations')->cascadeOnDelete();

            $table->primary(['green_work_id', 'recommendation_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('green_work_recommendations');
    }
};
