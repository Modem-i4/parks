<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->nullableMorphs('model'); 
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action');
            $table->json('before')->nullable(); 
            $table->json('after')->nullable();
            $table->timestamps();

            $table->index(['model_type','model_id','created_at']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
