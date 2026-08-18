<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $inventoryTags = require database_path('data/TreeInventoryTags.php');
        $updatedAt = now();

        DB::transaction(function () use ($inventoryTags, $updatedAt) {
            foreach ($inventoryTags as $inventoryNumber => $inventoryTag) {
                DB::table('trees')
                    ->whereIn('id', DB::table('green')
                        ->select('id')
                        ->where('inventory_number', (string) $inventoryNumber))
                    ->update([
                        'inventory_tag' => (string) $inventoryTag,
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }

    public function down(): void
    {
        $inventoryTags = require database_path('data/TreeInventoryTags.php');
        $updatedAt = now();

        DB::transaction(function () use ($inventoryTags, $updatedAt) {
            foreach ($inventoryTags as $inventoryNumber => $inventoryTag) {
                DB::table('trees')
                    ->whereIn('id', DB::table('green')
                        ->select('id')
                        ->where('inventory_number', (string) $inventoryNumber))
                    ->where('inventory_tag', (string) $inventoryTag)
                    ->update([
                        'inventory_tag' => null,
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }
};
