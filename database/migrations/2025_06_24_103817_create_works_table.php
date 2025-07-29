<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('works', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_id')->constrained('green')->cascadeOnDelete();
            $table->foreignId('recommendation_id')->constrained('recommendations')->cascadeOnDelete();
            $table->date('recommendation_date')->default(DB::raw('CURRENT_DATE'));
            $table->foreignId('recommender_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->date('execution_date')->nullable();
            $table->foreignId('executor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('works');
    }
};
