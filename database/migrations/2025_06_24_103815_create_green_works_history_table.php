<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('green_works_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_id')->constrained('green')->cascadeOnDelete();
            $table->date('recommendation_date');
            $table->date('execution_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('green_works_history');
    }
};
