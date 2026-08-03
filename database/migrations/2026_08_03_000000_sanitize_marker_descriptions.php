<?php

use App\Http\Services\SanitizeMarkerDescriptionService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sanitizer = app(SanitizeMarkerDescriptionService::class);

        DB::table('markers')
            ->whereNotNull('description')
            ->orderBy('id')
            ->chunkById(500, function ($markers) use ($sanitizer) {
                foreach ($markers as $marker) {
                    $description = $sanitizer->sanitize($marker->description);

                    if ($description !== $marker->description) {
                        DB::table('markers')
                            ->where('id', $marker->id)
                            ->update(['description' => $description]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Sanitization is intentionally irreversible.
    }
};
