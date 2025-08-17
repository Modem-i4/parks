<?php

namespace App\Http\Services\Export;

use App\Http\Services\Export\QmlGenerator;
use App\Http\Services\Export\SharedMarkerMapping;
use App\Http\Services\FieldNameCodec;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Process;
use ZipArchive;

class ShapefileExporter implements ExporterInterface
{
    use SharedMarkerMapping;

    private array $longFieldDump = [];
    private array $fieldManifest = [];

    public function export(Collection $markers): Response
    {
        if (!$this->ogr2ogrAvailable()) {
            return response()->json([
                'message' => 'GDAL (ogr2ogr) is not available on the server. Install GDAL to enable Shapefile export.'
            ], 500);
        }

        $uid     = Str::random(10);
        $workDir = storage_path("app/tmp_export_{$uid}");
        if (!is_dir($workDir)) mkdir($workDir, 0775, true);

        $basename    = "markers_{$uid}";
        $geojsonPath = "{$workDir}/{$basename}.geojson";
        $shpDir      = "{$workDir}/shp";
        mkdir($shpDir, 0775, true);

        $this->longFieldDump = [];
        $this->fieldManifest = FieldNameCodec::emptyManifest();

        $geojson = $this->buildGeoJsonForShp($markers);
        file_put_contents($geojsonPath, json_encode($geojson, JSON_UNESCAPED_UNICODE));

        $shpBase = "{$shpDir}/{$basename}.shp";
        $process = new Process([
            'ogr2ogr',
            '-f', 'ESRI Shapefile',
            '-t_srs', 'EPSG:4326',
            '-lco', 'ENCODING=UTF-8',
            $shpBase,
            $geojsonPath,
        ]);
        $process->setTimeout(60)->run();

        if (!$process->isSuccessful()) {
            $this->cleanupDir($workDir);
            return response()->json(['message' => 'ogr2ogr failed: '.$process->getErrorOutput()], 500);
        }

        $cpgPath = "{$shpDir}/{$basename}.cpg";
        if (!file_exists($cpgPath)) file_put_contents($cpgPath, "UTF-8");

        $prjPath = "{$shpDir}/{$basename}.prj";
        if (!file_exists($prjPath)) {
            $wkt = 'GEOGCS["WGS 84",DATUM["WGS_1984",SPHEROID["WGS 84",6378137,298.257223563]],PRIMEM["Greenwich",0],UNIT["degree",0.0174532925199433]]';
            file_put_contents($prjPath, $wkt);
        }

        [$colorField, $symbolField, $sizeField] = $this->detectActualFieldNames("{$shpDir}/{$basename}.dbf");

        $qmlPath = "{$shpDir}/{$basename}.qml";
        file_put_contents($qmlPath, QmlGenerator::generate($colorField, $symbolField, $sizeField));

        $fieldsJsonPath = "{$shpDir}/{$basename}.fields.json";
        file_put_contents($fieldsJsonPath, json_encode($this->fieldManifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $longJsonPath = null;
        if (!empty($this->longFieldDump)) {
            $longJsonPath = "{$shpDir}/{$basename}.long.json";
            $payload = [
                'meta' => [
                    'type'      => 'shapefile_sidecar',
                    'dbf_limit' => 254,
                    'fields'    => FieldNameCodec::SIDELOAD,
                    'version'   => 1,
                ],
                'data' => $this->longFieldDump,
            ];
            file_put_contents($longJsonPath, json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }

        $zipPath = "{$workDir}/{$basename}.zip";
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $this->cleanupDir($workDir);
            return response()->json(['message' => 'Failed to create zip archive'], 500);
        }
        foreach (['shp','shx','dbf','prj','cpg','qml','qmd','fix','sbx','sbn','fields.json'] as $ext) {
            $p = "{$shpDir}/{$basename}.{$ext}";
            if (file_exists($p)) {
                $zip->addFile($p, "{$basename}.{$ext}");
            }
        }
        if ($longJsonPath && file_exists($longJsonPath)) {
            $zip->addFile($longJsonPath, "{$basename}.long.json");
        }
        $zip->close();

        return response()->streamDownload(function () use ($zipPath, $workDir) {
            $fp = fopen($zipPath, 'rb');
            if ($fp) { fpassthru($fp); fclose($fp); }
            @unlink($zipPath);
            $this->cleanupDir($workDir);
        }, 'markers_shapefile.zip', ['Content-Type' => 'application/zip']);
    }

    private function buildGeoJsonForShp(Collection $markers): array
    {
        $features = $markers->map(function ($m) {
            $geom = $this->pointFromCoordinates($m->coordinates);
            if (!$geom) return null;

            $props = $this->markerToProperties($m);

            $this->moveFieldToSidecar($m->id, $props, 'tags');
            $this->moveFieldToSidecar($m->id, $props, 'works');

            if ($m->type === 'green' && $m->green) {
                $tree = $m->green->tree ?? null;
                if ($tree) {
                    $props['tree_height_m'] = $tree->height_m;
                    $props['tree_trunk_diameter_cm'] = $tree->trunk_diameter_cm;
                    $props['tree_trunk_circumference_cm'] = $tree->trunk_circumference_cm;
                    $props['tree_tilt_degree'] = $tree->tilt_degree;
                    $props['tree_crown_condition_percent'] = $tree->crown_condition_percent;
                }
                $bush = $m->green->bush ?? null;
                if ($bush) {
                    $props['bush_quantity'] = $bush->quantity;
                }
                $hedge = $m->green->hedge ?? null;
                if ($hedge) {
                    $props['hedge_length_m'] = $hedge->length_m;
                    $props['hedge_hedge_type_row'] = $hedge->hedge_type_row;
                    $props['hedge_hedge_type_shape'] = $hedge->hedge_type_shape;
                }
                $flower = $m->green->flower ?? null;
                if ($flower) {
                }
            }

            if ($m->type === 'infrastructure' && $m->infrastructure) {
                $it = $m->infrastructure->infrastructureType ?? null;
                if ($it) {
                    $props['infrastructureType_name'] = $it->name;
                    $props['infrastructureType_description'] = $it->description;
                }
            }

            $props['m_color']  = $this->markerColor($m);
            $props['m_symbol'] = $this->markerSymbol($m);
            $props['m_size']   = ($m->type === 'infrastructure') ? 'large' : 'medium';

            unset($props['marker-color'], $props['marker-symbol'], $props['marker-size']);
            $props['meta_ver'] = $this->meta()['version'] ?? 1;

            $shortProps = FieldNameCodec::encodeProps($props, $this->fieldManifest);

            return [
                'type'       => 'Feature',
                'id'         => $m->id,
                'geometry'   => $geom,
                'properties' => $shortProps,
            ];
        })->filter()->values()->all();

        return ['type' => 'FeatureCollection', 'features' => $features];
    }

    private function moveFieldToSidecar(int|string $markerId, array &$props, string $key): void
    {
        if (!array_key_exists($key, $props)) return;
        if (!isset($this->longFieldDump[$markerId])) $this->longFieldDump[$markerId] = [];
        $this->longFieldDump[$markerId][$key] = $props[$key];
        unset($props[$key]);
    }

    private function ogr2ogrAvailable(): bool
    {
        try {
            $p = new Process(['ogr2ogr', '--version']);
            $p->setTimeout(10)->run();
            return $p->isSuccessful();
        } catch (\Throwable $e) {
            return false;
        }
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

    private function detectActualFieldNames(string $dbfPath): array
    {
        $color = 'm_color';
        $symbol = 'm_symbol';
        $size = 'm_size';

        if (!is_readable($dbfPath)) {
            return [$color, $symbol, $size];
        }

        $fp = fopen($dbfPath, 'rb');
        if (!$fp) return [$color, $symbol, $size];

        fseek($fp, 32);
        while (!feof($fp)) {
            $chunk = fread($fp, 32);
            if ($chunk === false || strlen($chunk) < 32) break;
            if (ord($chunk[0]) === 0x0D) break;

            $nameRaw = rtrim(substr($chunk, 0, 11), "\0 \t\r\n");
            if ($nameRaw === '') continue;

            $name = strtolower($nameRaw);
            if ($name === 'm_color')  $color  = $nameRaw;
            if ($name === 'm_symbol') $symbol = $nameRaw;
            if ($name === 'm_size')   $size   = $nameRaw;
        }
        fclose($fp);

        return [$color, $symbol, $size];
    }
}
