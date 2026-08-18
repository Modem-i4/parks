<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $numbers = require database_path('data/GreenInventoryNumbers.php');
        $updatedAt = now();

        DB::transaction(function () use ($numbers, $updatedAt) {
            foreach ($numbers as $oldNumber => $newNumber) {
                $oldNumber = (string) $oldNumber;

                DB::table('green')
                    ->where('inventory_number', $oldNumber)
                    ->whereNull('inventory_number_old')
                    ->update([
                        'inventory_number_old' => $oldNumber,
                    ]);
            }

            foreach ($numbers as $oldNumber => $newNumber) {
                DB::table('green')
                    ->where('inventory_number_old', (string) $oldNumber)
                    ->update([
                        'inventory_number' => (string) $newNumber,
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }

    public function down(): void
    {
        $numbers = require database_path('data/GreenInventoryNumbers.php');
        $updatedAt = now();

        DB::transaction(function () use ($numbers, $updatedAt) {
            foreach ($numbers as $oldNumber => $newNumber) {
                DB::table('green')
                    ->where('inventory_number_old', (string) $oldNumber)
                    ->where('inventory_number', (string) $newNumber)
                    ->update([
                        'inventory_number' => (string) $oldNumber,
                        'inventory_number_old' => null,
                        'updated_at' => $updatedAt,
                    ]);
            }
        });
    }
};
