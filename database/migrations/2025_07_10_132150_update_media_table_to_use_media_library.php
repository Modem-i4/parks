<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn(['file_path', 'type']);

            $table->foreignId('media_library_id')
                ->after('id')
                ->constrained('media_library')
                ->cascadeOnDelete();

            $table->index(['model_type', 'model_id']);
        });
    }

    public function down(): void
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign(['media_library_id']);
            $table->dropColumn('media_library_id');

            $table->string('file_path');
            $table->enum('type', ['image', 'video', 'audio'])->default('image');

            $table->dropIndex(['model_type', 'model_id']);
        });
    }
};
