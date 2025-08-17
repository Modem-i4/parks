<?php

namespace App\Http\Services\Import;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

class GeoJsonImporter extends BaseImporter implements ImporterInterface
{
    public function import(UploadedFile $file, string $mode): array
    {
        $mode = strtolower($mode);
        if (!in_array($mode, ['create','update','create_update'], true)) {
            $mode = 'update';
        }

        $json = json_decode(file_get_contents($file->getRealPath()), true);
        if (!is_array($json) || !isset($json['features']) || !is_array($json['features'])) {
            return ['ok' => false, 'error' => 'Invalid GeoJSON'];
        }

        return $this->txn(function () use ($json, $mode) {
            $res = ['ok' => true, 'created' => 0, 'updated' => 0, 'errors' => []];

            foreach ($this->rowsPipeline($json) as $r) {
                if (isset($r['error'])) {
                    $res['errors'][] = ['row' => $r['rowId'], 'error' => $r['error']];
                    continue;
                }

                [$markerData, $greenData, $infraData, $rel] = $r['payload'];

                // Режими: create — тільки нові; update — тільки існуючі; create_update — все
                $exists = (bool) $this->markerByKey($markerData, $greenData);
                if (($exists && $mode === 'create') || (!$exists && $mode === 'update')) {
                    continue;
                }

                try {
                    $created = $this->upsertAll($markerData, $greenData, $infraData, $rel);
                    $res[$created ? 'created' : 'updated']++;
                } catch (\Throwable $e) {
                    $res['errors'][] = ['row' => $r['rowId'], 'error' => $e->getMessage()];
                }
            }

            return $res;
        });
    }

    public function preview(UploadedFile $file): array
    {
        $json = json_decode(file_get_contents($file->getRealPath()), true);
        if (!is_array($json) || !isset($json['features']) || !is_array($json['features'])) {
            return ['ok' => false, 'error' => 'Invalid GeoJSON'];
        }

        $res = ['ok' => true, 'will_create' => 0, 'will_update' => 0, 'errors' => []];

        foreach ($this->rowsPipeline($json) as $r) {
            if (isset($r['error'])) {
                $res['errors'][] = ['row' => $r['rowId'], 'error' => $r['error']];
                continue;
            }

            [$markerData, $greenData] = $r['payload'];

            try {
                $exists = (bool) $this->markerByKey($markerData, $greenData);
                $exists ? $res['will_update']++ : $res['will_create']++;
            } catch (\Throwable $e) {
                $res['errors'][] = ['row' => $r['rowId'], 'error' => $e->getMessage()];
            }
        }

        return $res;
    }

    private function rowsPipeline(array $json): \Generator
    {
        foreach ($json['features'] as $f) {
            $rowId   = Arr::get($f, 'id');
            $geom    = Arr::get($f, 'geometry.type');
            $coords  = Arr::get($f, 'geometry.coordinates');
            $props   = Arr::get($f, 'properties', []);

            if ($geom !== 'Point') {
                yield ['rowId' => $rowId, 'error' => 'Unsupported geometry'];
                continue;
            }

            if (!is_array($coords) || count($coords) !== 2) {
                yield ['rowId' => $rowId, 'error' => 'Invalid coordinates'];
                continue;
            }

            if (isset($props['__meta']) && is_string($props['__meta'])) {
                $d = json_decode($props['__meta'], true);
                if (json_last_error() === JSON_ERROR_NONE) $props['__meta'] = $d;
            }

            if (isset($props['tags']) && is_string($props['tags'])) {
                $d = json_decode($props['tags'], true);
                $props['tags'] = json_last_error() === JSON_ERROR_NONE ? $d : [];
            }

            $id = $rowId ?? ($props['id'] ?? null);
            [$markerData, $greenData, $infraData, $rel] = $this->splitProps($props, $coords);
            if ($id) $markerData['id'] = $id;

            if (!isset($markerData['park_id']) && isset($props['park_id'])) {
                $markerData['park_id'] = is_numeric($props['park_id']) ? (int)$props['park_id'] : null;
            }

            if (empty($markerData['park_id'])) {
                yield ['rowId' => $rowId, 'error' => 'park_id відсутній або порожній'];
                continue;
            }

            yield ['rowId' => $rowId, 'payload' => [$markerData, $greenData, $infraData, $rel]];
        }
    }
}
