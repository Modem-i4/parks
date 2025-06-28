<?php

namespace App\Http\Controllers;

use App\Models\Marker;
use App\Models\Park;

class MarkerController extends Controller
{
    public function getParkMarkers($id, $type = null)
    {
        $park = Park::with('icon')->findOrFail($id);
        $center = $park->coordinates;
        $markers = $this->getMarkers($id, $type);
        if (!$center) {
            return $markers;
        }
        
        $markers = $this->addDistanceToCenter($markers, $center);
        return $markers->sortBy('distanceToCenter')->values();
    }

    protected function getMarkers($parkId, $type = null)
    {
        return Marker::with('icon')
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId)
            ->when($type, fn($query) => $query->where('type', $type))
            ->get();
    }

    protected function addDistanceToCenter($markers, array $center)
    {
        [$centerLng, $centerLat] = $center;

        $calculateDistance = function ($lng1, $lat1, $lng2, $lat2) {
            $dx = $lng2 - $lng1;
            $dy = $lat2 - $lat1;
            return sqrt($dx * $dx + $dy * $dy);
        };

        $markers->each(function ($marker) use ($calculateDistance, $centerLng, $centerLat) {
            if (!empty($marker->coordinates) && count($marker->coordinates) === 2) {
                [$markerLng, $markerLat] = $marker->coordinates;
                $marker->distanceToCenter = $calculateDistance($centerLng, $centerLat, $markerLng, $markerLat);
            } else {
                $marker->distanceToCenter = INF;
            }
        });

        return $markers;
    }
}
