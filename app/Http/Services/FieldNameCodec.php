<?php

namespace App\Http\Services;

final class FieldNameCodec
{
    public const MAX = 10;

    private const FIXED = [
        'id' => 'id',
        'type' => 'type',
        'meta_ver' => 'meta_ver',
        'm_color' => 'm_color',
        'm_symbol' => 'm_symbol',
        'm_size' => 'm_size',

        'park_id' => 'park_id',
        'plot_id' => 'plot_id',
        'description' => 'descr',

        'inventory_number' => 'inv_number',
        'species_id' => 'species_id',
        'planting_date' => 'plant_date',
        'quality_state' => 'qual_state',
        'quality_state_note' => 'qual_note',

        'tree_height_m' => 'tree_h_m',
        'tree_trunk_circumference_cm' => 'tree_tc_cm',
        'tree_tilt_degree' => 'tree_t_deg',
        'tree_crown_condition_percent' => 'tree_ccpct',

        'bush_quantity' => 'bush_qty',

        'hedge_length_m' => 'hedge_len',
        'hedge_hedge_type_row' => 'hedge_row',
        'hedge_hedge_type_shape' => 'hedge_shp',

        'name' => 'infra_name',
        'infrastructure_type' => 'infra_type',

        'infrastructureType_name' => 'it_name',
        'infrastructureType_description' => 'it_descr',

        'marker-color' => 'm_color',
        'marker-symbol' => 'm_symbol',
        'marker-size' => 'm_size',
    ];

    public const SIDELOAD = ['tags','works'];

    private const AUTO_PREFIX = 'f_';

    public static function encodeProps(array $props, array &$manifest): array
    {
        $manifest = $manifest ?: self::emptyManifest();
        $out = [];
        foreach ($props as $k => $v) {
            if (in_array($k, self::SIDELOAD, true)) {
                continue;
            }
            $short = self::shortName($k);
            $out[$short] = $v;
            $manifest['map'][$k] = $short;
            $manifest['reverse'][$short] = $k;
        }
        return $out;
    }

    public static function decodeProps(array $props, ?array $manifest = null): array
    {
        if ($manifest && isset($manifest['reverse']) && is_array($manifest['reverse'])) {
            $rev = $manifest['reverse'];
            $out = [];
            foreach ($props as $k => $v) {
                $canon = $rev[$k] ?? self::fromHeuristics($k);
                $out[$canon] = $v;
            }
            return $out;
        }
        $fixRev = array_flip(self::FIXED);
        $out = [];
        foreach ($props as $k => $v) {
            $canon = $fixRev[$k] ?? self::fromHeuristics($k);
            $out[$canon] = $v;
        }
        return $out;
    }

    public static function emptyManifest(): array
    {
        return [
            'meta' => ['type' => 'shapefile_fieldmap', 'version' => 1],
            'map' => [],
            'reverse' => [],
        ];
    }

    private static function shortName(string $name): string
    {
        if (isset(self::FIXED[$name])) {
            return self::FIXED[$name];
        }
        $safe = strtolower(preg_replace('/[^a-z0-9_]/i', '_', $name));
        if (strlen($safe) <= self::MAX) {
            return $safe;
        }
        $h = base_convert(sprintf('%u', crc32($name)), 10, 36);
        $short = self::AUTO_PREFIX . substr($h, 0, self::MAX - strlen(self::AUTO_PREFIX));
        return $short;
    }

    private static function fromHeuristics(string $short): string
    {
        $fixRev = array_flip(self::FIXED);
        return $fixRev[$short] ?? $short;
    }
}
