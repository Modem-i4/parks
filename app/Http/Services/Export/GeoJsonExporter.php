<?php

namespace App\Http\Services\Export;

use App\Http\Services\Export\SharedMarkerMapping;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class GeoJsonExporter implements ExporterInterface
{
    use SharedMarkerMapping;

    public function export(Collection $markers): Response
    {
        $geojson = $this->buildGeoJson($markers);

        return response()->json(
            $geojson,
            200,
            [
                'Content-Disposition' => 'attachment; filename="markers.geojson"',
                'Content-Type'        => 'application/geo+json; charset=utf-8',
            ]
        );
    }

    public function buildGeoJson(Collection $markers): array
    {
        $features = $markers->map(function ($m) {
            $geom = $this->pointFromCoordinates($m->coordinates);
            if (!$geom) return null;

            $props = $this->markerToProperties($m);
            $props['marker-color']  = $this->markerColor($m);
            $props['marker-symbol'] = $this->markerSymbol($m);
            $props['marker-size']   = ($m->type === 'infrastructure') ? 'large' : 'medium';
            $props['__meta']        = array_merge($this->meta(), ['marker_type' => $m->type ?? null]);

            return [
                'type'       => 'Feature',
                'id'         => $m->id,
                'geometry'   => $geom,
                'properties' => $props,
            ];
        })->filter()->values()->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }
}
