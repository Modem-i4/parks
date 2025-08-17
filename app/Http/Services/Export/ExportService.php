<?php

namespace App\Http\Services\Export;

use App\Http\Controllers\MarkerController;
use App\Models\Marker;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

class ExportService
{
    public function __construct(
        private readonly GeoJsonExporter   $geoJsonExporter,
        private readonly CsvExporter       $csvExporter,
        private readonly ShapefileExporter $shapefileExporter,
    ) {}

    public function export(array $ids, string $format = 'geojson'): Response
    {
        $ids = array_values(Arr::wrap($ids));
        if (empty($ids)) {
            return response()->json(['message' => 'No markers provided'], 422);
        }

        $markers = Marker::with(MarkerController::markerFields)
            ->whereIn('id', $ids)
            ->get();

        if ($markers->isEmpty()) {
            return response()->json(['message' => 'Markers not found'], 404);
        }

        return $this->resolveExporter($format)->export($markers);
    }

    private function resolveExporter(string $format): ExporterInterface
    {
        return match (strtolower($format)) {
            'csv'              => $this->csvExporter,
            'shp', 'shapefile' => $this->shapefileExporter,
            default            => $this->geoJsonExporter,
        };
    }
}
