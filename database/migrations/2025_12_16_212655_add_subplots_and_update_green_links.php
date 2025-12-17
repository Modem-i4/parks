<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subplots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plot_id')->constrained('plots')->cascadeOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('green', function (Blueprint $table) {
            $table->foreignId('subplot_id')->nullable()->after('plot_id');
        });

        DB::table('subplots')->insertUsing(
            ['plot_id','name','created_at','updated_at'],
            DB::table('plots')->select(['id','name', DB::raw('NOW()'), DB::raw('NOW()')])
        );

        DB::table('green as g')
            ->join('subplots as s', 's.plot_id', '=', 'g.plot_id')
            ->whereNotNull('g.plot_id')
            ->update(['g.subplot_id' => DB::raw('s.id')]);

        Schema::table('green', function (Blueprint $table) {
            $table->dropForeign(['plot_id']);
            $table->dropColumn('plot_id');
        });

        Schema::table('green', function (Blueprint $table) {
            $table->foreign('subplot_id')->references('id')->on('subplots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('green', function (Blueprint $table) {
            $table->foreignId('plot_id')->nullable()->after('inventory_number');
        });

        DB::table('green as g')
            ->join('subplots as s', 's.id', '=', 'g.subplot_id')
            ->whereNotNull('g.subplot_id')
            ->update(['g.plot_id' => DB::raw('s.plot_id')]);

        Schema::table('green', function (Blueprint $table) {
            $table->dropForeign(['subplot_id']);
            $table->dropColumn('subplot_id');
        });

        Schema::dropIfExists('subplots');

        Schema::table('green', function (Blueprint $table) {
            $table->foreign('plot_id')->references('id')->on('plots')->nullOnDelete();
        });
    }
};
