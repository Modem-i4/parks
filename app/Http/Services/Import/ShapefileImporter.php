<?php

namespace App\Http\Services\Import;

use App\Http\Services\FieldNameCodec;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class ShapefileImporter extends BaseImporter implements ImporterInterface
{
    public function import(UploadedFile $file, string $mode): array
    {
        $mode = strtolower($mode);
        if (!in_array($mode, ['create','update','create_update'], true)) {
            $mode = 'update';
        }

        $uid     = Str::random(10);
        $workDir = storage_path("app/tmp_import_{$uid}");
        if (!is_dir($workDir)) mkdir($workDir, 0775, true);

        $zipPath = "{$workDir}/upload.zip";
        $file->move($workDir, 'upload.zip');

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($workDir);
            $zip->close();
        }

        $sidecar  = $this->readSidecar($workDir);
        $fieldMap = $this->readFieldManifest($workDir);

        $geojsonPath = "{$workDir}/converted.geojson";
        $process = new Process([
            'ogr2ogr',
            '-f', 'GeoJSON',
            $geojsonPath,
            $workDir,
        ]);
        $process->setTimeout(60)->run();

        if (!$process->isSuccessful() || !file_exists($geojsonPath)) {
            $this->cleanupDir($workDir);
            return ['ok' => false, 'error' => 'Failed to convert shapefile to GeoJSON: '.$process->getErrorOutput()];
        }

        $json = json_decode(file_get_contents($geojsonPath), true);
        if (!is_array($json) || !isset($json['features']) || !is_array($json['features'])) {
            $this->cleanupDir($workDir);
            return ['ok' => false, 'error' => 'Invalid converted GeoJSON'];
        }

        $res = $this->txn(function () use ($json, $sidecar, $fieldMap, $mode) {
            $res = ['ok' => true, 'created' => 0, 'updated' => 0, 'errors' => []];

            foreach ($this->rowsPipeline($json, $sidecar, $fieldMap) as $r) {
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

        $this->cleanupDir($workDir);
        return $res;
    }

    public function preview(UploadedFile $file): array
    {
        $uid     = Str::random(10);
        $workDir = storage_path("app/tmp_import_{$uid}");
        if (!is_dir($workDir)) mkdir($workDir, 0775, true);

        $zipPath = "{$workDir}/upload.zip";
        $file->move($workDir, 'upload.zip');

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($workDir);
            $zip->close();
        }

        $sidecar  = $this->readSidecar($workDir);
        $fieldMap = $this->readFieldManifest($workDir);

        $geojsonPath = "{$workDir}/converted.geojson";
        $process = new Process([
            'ogr2ogr',
            '-f', 'GeoJSON',
            $geojsonPath,
            $workDir,
        ]);
        $process->setTimeout(60)->run();

        if (!$process->isSuccessful() || !file_exists($geojsonPath)) {
            $this->cleanupDir($workDir);
            return ['ok' => false, 'error' => 'Failed to convert shapefile to GeoJSON: '.$process->getErrorOutput()];
        }

        $json = json_decode(file_get_contents($geojsonPath), true);
        if (!is_array($json) || !isset($json['features']) || !is_array($json['features'])) {
            $this->cleanupDir($workDir);
            return ['ok' => false, 'error' => 'Invalid converted GeoJSON'];
        }

        $res = ['ok' => true, 'will_create' => 0, 'will_update' => 0, 'errors' => []];

        foreach ($this->rowsPipeline($json, $sidecar, $fieldMap) as $r) {
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

        $this->cleanupDir($workDir);
        return $res;
    }

    private function rowsPipeline(array $json, array $sidecar, ?array $fieldMap): \Generator
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

            $props = FieldNameCodec::decodeProps($props, $fieldMap);

            if (isset($props['__meta']) && is_string($props['__meta'])) {
                $d = json_decode($props['__meta'], true);
                if (json_last_error() === JSON_ERROR_NONE) $props['__meta'] = $d;
            }

            if (isset($props['tags']) && is_string($props['tags'])) {
                $d = json_decode($props['tags'], true);
                $props['tags'] = json_last_error() === JSON_ERROR_NONE ? $d : [];
            }

            $id = $rowId ?? ($props['id'] ?? null);

            if ($id !== null) {
                $sid = (string) $id;
                if (isset($sidecar[$sid])) {
                    if (array_key_exists('tags', $sidecar[$sid]))  $props['tags']  = $sidecar[$sid]['tags'];
                    if (array_key_exists('works', $sidecar[$sid])) $props['works'] = $sidecar[$sid]['works'];
                }
            }

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

    private function readSidecar(string $dir): array
    {
        $map = [];
        $it = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $f) {
            if (!$f->isFile()) continue;
            $name = $f->getFilename();
            if (!preg_match('/\.long\.json$/i', $name)) continue;
            $data = json_decode(@file_get_contents($f->getRealPath()), true);
            if (!is_array($data)) continue;
            $payload = $data['data'] ?? null;
            if (!is_array($payload)) continue;
            foreach ($payload as $id => $vals) {
                $sid = (string) $id;
                if (!isset($map[$sid])) $map[$sid] = [];
                if (array_key_exists('tags', $vals))  $map[$sid]['tags']  = $vals['tags'];
                if (array_key_exists('works', $vals)) $map[$sid]['works'] = $vals['works'];
            }
        }
        return $map;
    }

    private function readFieldManifest(string $dir): ?array
    {
        $it = new \FilesystemIterator($dir, \FilesystemIterator::SKIP_DOTS);
        foreach ($it as $f) {
            if (!$f->isFile()) continue;
            $name = $f->getFilename();
            if (!preg_match('/\.fields\.json$/i', $name)) continue;
            $data = json_decode(@file_get_contents($f->getRealPath()), true);
            if (is_array($data) && ($data['meta']['type'] ?? null) === 'shapefile_fieldmap') {
                return $data;
            }
        }
        return null;
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) return;
        $it = new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($it, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? @rmdir($file->getRealPath()) : @unlink($file->getRealPath());
        }
        @rmdir($dir);
    }
}
