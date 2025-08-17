<?php

namespace App\Http\Services\Import;

use Illuminate\Http\UploadedFile;
use League\Csv\Reader;

class CsvImporter extends BaseImporter implements ImporterInterface
{
    public function import(UploadedFile $file, string $mode): array
    {
        $mode = strtolower($mode);
        if (!in_array($mode, ['create','update','create_update'], true)) {
            $mode = 'update';
        }

        return $this->txn(function () use ($file, $mode) {
            $res = ['ok' => true, 'created' => 0, 'updated' => 0, 'errors' => []];

            foreach ($this->rowsPipeline($file) as $r) {
                if (isset($r['error'])) {
                    $res['errors'][] = ['row' => $r['rowId'], 'error' => $r['error']];
                    continue;
                }

                [$markerData, $greenData, $infraData, $rel] = $r['payload'];

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
        $res = ['ok' => true, 'will_create' => 0, 'will_update' => 0, 'errors' => []];

        foreach ($this->rowsPipeline($file) as $r) {
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

    private function rowsPipeline(UploadedFile $file): \Generator
    {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        $csv->setEnclosure('"');

        foreach ($csv->getRecords() as $row) {
            $rowId = $row['id'] ?? null;

            foreach ($row as $k => $v) {
                if (is_string($v)) {
                    $v = trim($v);
                    if ($v === '') $v = null;
                }
                $row[$k] = $v;
            }

            if (isset($row['__meta']) && is_string($row['__meta'])) {
                $d = json_decode($row['__meta'], true);
                if (json_last_error() === JSON_ERROR_NONE) $row['__meta'] = $d;
            }

            if (isset($row['tags']) && is_string($row['tags'])) {
                $d = json_decode($row['tags'], true);
                $row['tags'] = json_last_error() === JSON_ERROR_NONE ? $d : [];
            }

            $lat = $row['lat'] ?? null;
            $lng = $row['lng'] ?? null;
            $coords = (is_numeric($lng) && is_numeric($lat)) ? [(float)$lng, (float)$lat] : null;

            [$markerData, $greenData, $infraData, $rel] = $this->splitProps($row, $coords);

            if (!empty($row['id'])) $markerData['id'] = $row['id'];

            if (!isset($markerData['park_id']) && isset($row['park_id'])) {
                $markerData['park_id'] = is_numeric($row['park_id']) ? (int)$row['park_id'] : null;
            }

            if (empty($markerData['park_id'])) {
                yield ['rowId' => $rowId, 'error' => 'park_id відсутній або порожній'];
                continue;
            }

            yield ['rowId' => $rowId, 'payload' => [$markerData, $greenData, $infraData, $rel]];
        }
    }
}
