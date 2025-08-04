<?php

use App\Enums\QualityState;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('green', function (Blueprint $table) {
            $table->id()->constrained('markers');
            $table->string('inventory_number')->nullable();
            $table->foreignId('plot_id')->nullable()->constrained('plots')->nullOnDelete();
            $table->foreignId('species_id')->nullable()->constrained('species')->cascadeOnDelete();
            $table->date('planting_date')->nullable();
            $table->enum('quality_state', QualityState::values());
            $table->text('quality_state_note')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('green');
    }
};
