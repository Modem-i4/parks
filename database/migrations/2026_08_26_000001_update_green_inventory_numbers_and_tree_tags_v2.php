<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $mapping = require database_path('data/GreenInvTagMapping_v2.php');
        $updatedAt = now();

        DB::transaction(function () use ($mapping, $updatedAt) {
            $green = DB::table('green')
                ->whereIn('inventory_number', array_keys($mapping))
                ->get(['id', 'inventory_number']);

            foreach ($green as $item) {
                $mapped = $mapping[(string) $item->inventory_number];

                DB::table('green')
                    ->where('id', $item->id)
                    ->update([
                        'inventory_number' => (string) $mapped['inv'],
                        'updated_at' => $updatedAt,
                    ]);

                DB::table('trees')
                    ->where('id', $item->id)
                    ->update([
                        'inventory_tag' => $mapped['tag'] === null
                            ? null
                            : (string) $mapped['tag'],
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }

    public function down(): void
    {
        $mapping = require database_path('data/GreenInvTagMapping_v2.php');
        $previousTags = require database_path('data/TreeInventoryTags.php');
        $reverseMapping = [];

        foreach ($mapping as $oldInventoryNumber => $mapped) {
            $reverseMapping[(string) $mapped['inv']] = (string) $oldInventoryNumber;
        }

        $updatedAt = now();

        DB::transaction(function () use ($reverseMapping, $previousTags, $updatedAt) {
            $green = DB::table('green')
                ->whereIn('inventory_number', array_keys($reverseMapping))
                ->get(['id', 'inventory_number']);

            foreach ($green as $item) {
                $oldInventoryNumber = $reverseMapping[(string) $item->inventory_number];

                DB::table('green')
                    ->where('id', $item->id)
                    ->update([
                        'inventory_number' => $oldInventoryNumber,
                        'updated_at' => $updatedAt,
                    ]);

                DB::table('trees')
                    ->where('id', $item->id)
                    ->update([
                        'inventory_tag' => isset($previousTags[$oldInventoryNumber])
                            ? (string) $previousTags[$oldInventoryNumber]
                            : null,
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }
};
