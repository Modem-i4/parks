<?php

namespace App\Http\Services\Export;

trait SharedMarkerMapping
{
    private const META = ['version' => 1, 'flattened' => true];

    private function pointFromCoordinates($coordinates): ?array
    {
        if (is_string($coordinates)) {
            $decoded = json_decode($coordinates, true);
            if (json_last_error() === JSON_ERROR_NONE) $coordinates = $decoded;
        }
        if (is_array($coordinates) && count($coordinates) === 2 && is_numeric($coordinates[0]) && is_numeric($coordinates[1])) {
            $lng = (float)$coordinates[0];
            $lat = (float)$coordinates[1];
            return ['type' => 'Point', 'coordinates' => [$lng, $lat]];
        }
        return null;
    }

    private function markerToProperties($marker): array
    {
        $data = $marker->toArray();
        unset($data['coordinates'], $data['created_at'], $data['updated_at']);

        $props = $data;

        $green = $data['green'] ?? null;
        $infra = $data['infrastructure'] ?? null;

        if (is_array($green)) {
            $this->mergeScalars($props, $green);
            $this->mergePrefixed($props, $green['tree']   ?? null, 'tree');
            $this->mergePrefixed($props, $green['bush']   ?? null, 'bush');
            $this->mergePrefixed($props, $green['hedge']  ?? null, 'hedge');
            $this->mergePrefixed($props, $green['flower'] ?? null, 'flower');
            $this->mergePrefixed($props, $green['plot']   ?? null, 'plot');

            if (!empty($green['species'])) {
                $sp = $green['species'];
                $props['species_id']       = $props['species_id'] ?? ($sp['id'] ?? null);
                $props['species_name_ukr'] = $sp['name_ukr'] ?? null;
                $props['species_name_lat'] = $sp['name_lat'] ?? null;

                if (!empty($sp['genus'])) {
                    $gn = $sp['genus'];
                    $props['genus_id']       = $props['genus_id'] ?? ($gn['id'] ?? null);
                    $props['genus_name_ukr'] = $gn['name_ukr'] ?? null;
                    $props['genus_name_lat'] = $gn['name_lat'] ?? null;

                    if (!empty($gn['family'])) {
                        $fm = $gn['family'];
                        $props['family_id']       = $props['family_id'] ?? ($fm['id'] ?? null);
                        $props['family_name_ukr'] = $fm['name_ukr'] ?? null;
                        $props['family_name_lat'] = $fm['name_lat'] ?? null;
                    }
                }
            }

            unset($props['green']);
        }

        if (is_array($infra)) {
            $this->mergeScalars($props, $infra);
            $this->mergePrefixed($props, $infra['infrastructureType'] ?? null, 'infrastructureType');
            unset($props['infrastructure']);
        }

        return $props;
    }

    private function mergeScalars(array &$dst, ?array $src): void
    {
        if (!is_array($src)) return;
        foreach ($src as $k => $v) {
            if (in_array($k, ['id','created_at','updated_at'], true)) continue;
            if (is_array($v) || is_object($v)) continue;
            if (!array_key_exists($k, $dst)) $dst[$k] = $v;
        }
    }

    private function mergePrefixed(array &$dst, ?array $src, string $prefix): void
    {
        if (!is_array($src)) return;
        foreach ($src as $k => $v) {
            if (in_array($k, ['id','created_at','updated_at'], true)) continue;
            if (is_array($v) || is_object($v)) continue;
            $key = "{$prefix}_{$k}";
            if (!array_key_exists($key, $dst)) $dst[$key] = $v;
        }
    }

    private function markerColor($marker): string
    {
        if (!empty($marker->green)) {
            $state = data_get($marker, 'green.green_state');
            return match ($state) {
                'good'    => 'green',
                'normal'  => '#fcd45b',
                'bad'     => 'red',
                'planned' => '#66a9ff',
                'removed' => '#9d9fa3',
                default   => 'gray',
            };
        }
        return '#000000';
    }

    private function markerSymbol($marker): string
    {
        $type = (string) ($marker->type ?? '');
        return match ($type) {
            'tree'   => 'circle',
            'bush'   => 'triangle',
            'hedge'  => 'rectangle',
            'flower' => 'diamond',
            default  => 'M',
        };
    }

    private function flattenProps(array $props): array
    {
        $flat = [];
        foreach ($props as $k => $v) {
            $flat[$k] = (is_array($v) || is_object($v))
                ? json_encode($v, JSON_UNESCAPED_UNICODE)
                : $v;
        }
        return $flat;
    }

    protected function meta(): array { return self::META; }
}
