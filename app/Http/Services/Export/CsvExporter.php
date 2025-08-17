<?php

namespace App\Http\Services\Export;

use App\Http\Services\Export\SharedMarkerMapping;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class CsvExporter implements ExporterInterface
{
    use SharedMarkerMapping;

    public function export(Collection $markers): Response
    {
        $rows = [];
        foreach ($markers as $m) {
            $geom = $this->pointFromCoordinates($m->coordinates);
            if (!$geom) continue;

            [$lng, $lat] = $geom['coordinates'];
            $props = $this->markerToProperties($m);
            $props['marker-color']  = $this->markerColor($m);
            $props['marker-symbol'] = $this->markerSymbol($m);
            $props['marker-size']   = ($m->type === 'infrastructure') ? 'large' : 'medium';
            $props['__meta']        = array_merge($this->meta(), ['marker_type' => $m->type ?? null]);

            $rows[] = array_merge([
                'id'  => $m->id,
                'lat' => $lat,
                'lng' => $lng,
            ], $this->flattenProps($props));
        }

        $headers = [];
        foreach ($rows as $r) foreach ($r as $k => $v) $headers[$k] = true;
        $headers = array_keys($headers);

        $callback = function () use ($headers, $rows) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headers, ';');
            foreach ($rows as $r) {
                $line = [];
                foreach ($headers as $h) {
                    $val = $r[$h] ?? null;
                    if (is_array($val) || is_object($val)) $val = json_encode($val, JSON_UNESCAPED_UNICODE);
                    $line[] = $val;
                }
                fputcsv($out, $line, ';');
            }
            fclose($out);
        };

        return response()->streamDownload($callback, 'markers.csv', [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }
}
