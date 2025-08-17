<?php

namespace App\Http\Services\Import;

use App\Models\Marker;
use App\Models\Green;
use App\Models\Infrastructure;
use App\Models\Tag;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class BaseImporter
{
    protected static array $__columns_cache = [];
    protected static array $__relation_columns_cache = [];

    protected function normalizeInventoryNumber(?string $inv): ?string
    {
        if ($inv === null) return null;
        $inv = trim($inv);
        return $inv === '' ? null : $inv;
    }

    protected function markerByKey(array $markerAttrs, ?array $greenData): ?Marker
    {
        $inv = $this->normalizeInventoryNumber($greenData['inventory_number'] ?? null);
        if ($inv !== null) {
            return Marker::withTrashed()
                ->whereHas('green', function ($q) use ($inv) {
                    $q->where('inventory_number', $inv);
                })
                ->first();
        }
        $id = $markerAttrs['id'] ?? null;
        if ($id) {
            return Marker::withTrashed()->find($id);
        }
        return null;
    }

    protected function uniqueKeyForGreen(array $attrs, Marker $marker): array
    {
        $inv = $this->normalizeInventoryNumber($attrs['inventory_number'] ?? null);
        if ($inv !== null) return ['inventory_number' => $inv];
        return ['id' => $marker->id];
    }

    protected function prepareGreenAttrs(array $attrs, Marker $marker): array
    {
        $attrs['id'] = $marker->id;
        if (array_key_exists('inventory_number', $attrs)) {
            $attrs['inventory_number'] = $this->normalizeInventoryNumber($attrs['inventory_number']);
        }
        return $attrs;
    }

    protected function uniqueKeyForTag(array $attrs): array
    {
        if (!empty($attrs['id'])) return ['id' => $attrs['id']];
        return ['name' => $attrs['name'] ?? null, 'type' => $attrs['type'] ?? null];
    }

    protected function upsertTags(Marker $marker, array $items): void
    {
        $tagCols = $this->columnsFor(new Tag);
        $ids = [];
        foreach ($items as $raw) {
            if (!is_array($raw)) continue;
            $attrs = $this->intersectByColumns($raw, $tagCols);
            if (empty($attrs)) continue;
            $tag = Tag::updateOrCreate(
                $this->uniqueKeyForTag($attrs),
                Arr::except($attrs, ['id'])
            );
            $ids[] = $tag->id;
        }
        $marker->tags()->sync($ids);
    }

    protected function txn(callable $fn)
    {
        DB::beginTransaction();
        try { $r = $fn(); DB::commit(); return $r; }
        catch (\Throwable $e) { DB::rollBack(); throw $e; }
    }

    protected function upsertAll(array $markerData, ?array $greenData, ?array $infraData, array $rel): bool
    {
        $markerCols  = $this->columnsFor(new Marker);
        $markerAttrs = $this->intersectByColumns($markerData, $markerCols);
        if (!empty($markerData['coordinates'])) $markerAttrs['coordinates'] = $markerData['coordinates'];

        $marker = $this->markerByKey($markerAttrs, $greenData);
        if ($marker) {
            if (method_exists($marker, 'trashed') && $marker->trashed()) {
                $marker->restore();
            }
            $marker->fill(Arr::except($markerAttrs, ['id']))->save();
            $created = false;
        } else {
            $marker = Marker::create(Arr::except($markerAttrs, ['id']));
            $created = true;
        }

        if ($greenData) {
            $greenCols  = $this->columnsFor(new Green);
            $greenAttrs = $this->intersectByColumns($greenData, $greenCols);
            $greenAttrs = $this->prepareGreenAttrs($greenAttrs, $marker);

            $marker->green()->updateOrCreate(
                $this->uniqueKeyForGreen($greenAttrs, $marker),
                Arr::except($greenAttrs, ['id'])
            );

            foreach (['tree','bush','hedge','flower'] as $name) {
                $data = $rel[$name] ?? [];
                $this->upsertChild1to1($marker->green, $name, $data);
            }
        }

        if ($infraData) {
            $infraCols  = $this->columnsFor(new Infrastructure);
            $infraAttrs = $this->intersectByColumns($infraData, $infraCols);
            $infraAttrs['id'] = $marker->id;

            $marker->infrastructure()->updateOrCreate(
                ['id' => $infraAttrs['id']],
                Arr::except($infraAttrs, ['id'])
            );

            if (!empty($rel['infrastructureType']) && method_exists($marker->infrastructure(), 'infrastructureType')) {
                $cols  = $this->columnsForRelation('infrastructureType');
                $attrs = $this->intersectByColumns($rel['infrastructureType'], $cols);
                if (!empty($attrs)) {
                    $marker->infrastructure->infrastructureType()->updateOrCreate(
                        ['id' => $attrs['id'] ?? null],
                        Arr::except($attrs, ['id'])
                    );
                }
            }
        }

        if (array_key_exists('tags', $rel)) {
            $this->upsertTags($marker, is_array($rel['tags']) ? $rel['tags'] : []);
        }

        return $created;
    }

    protected function upsertChild1to1($green, string $relation, array $data): void
    {
        if (!$green || empty($data) || !method_exists($green, $relation)) return;

        $cols  = $this->columnsForRelation($relation);
        $attrs = $this->intersectByColumns($data, $cols);
        if (empty($attrs)) return;

        $attrs['id'] ??= $green->id;

        $green->{$relation}()->updateOrCreate(
            ['id' => $attrs['id']],
            Arr::except($attrs, ['id'])
        );
    }

    protected function splitProps(array $props, ?array $coords): array
    {
        $markerType = Arr::get($props, '__meta.marker_type') ?? ($props['type'] ?? null);

        $tagsProvided = array_key_exists('tags', $props);
        $tags = $tagsProvided && is_array($props['tags']) ? array_values($props['tags']) : [];
        if ($tagsProvided) unset($props['tags']);

        $prefixed = [
            'tree'  => $this->unprefix($props, 'tree_'),
            'bush'  => $this->unprefix($props, 'bush_'),
            'hedge' => $this->unprefix($props, 'hedge_'),
            'flower'=> $this->unprefix($props, 'flower_'),
            'plot'  => $this->unprefix($props, 'plot_'),
            'infrastructureType' => $this->unprefix($props, 'infrastructureType_'),
        ];
        if ($tagsProvided) $prefixed['tags'] = $tags;

        $markerData = [
            'id'          => $props['id'] ?? null,
            'type'        => $markerType,
            'coordinates' => $coords,
        ];

        $greenData = null;
        $infraData = null;

        foreach (['__meta','marker-color','marker-symbol','marker-size','lat','lng'] as $r) unset($props[$r]);

        $markerCols = $this->columnsFor(new Marker);

        foreach ($props as $k => $v) {
            if (is_array($v) || is_object($v)) continue;

            if (in_array($k, $markerCols, true)) { $markerData[$k] = $v; continue; }

            if ($markerType === 'infrastructure') {
                $infraData ??= [];
                $infraData[$k] = $v;
            } else {
                $greenData ??= [];
                $greenData[$k] = $v;
            }
        }

        return [$markerData, $greenData, $infraData, $prefixed];
    }

    protected function unprefix(array &$src, string $prefix): array
    {
        $len = strlen($prefix);
        $out = [];
        foreach ($src as $k => $v) {
            if (strncmp($k, $prefix, $len) === 0 && !is_array($v) && !is_object($v)) {
                $out[substr($k, $len)] = $v;
                unset($src[$k]);
            }
        }
        return $out;
    }

    protected function columnsFor(object $model): array
    {
        $table = $model->getTable();
        if (!isset(self::$__columns_cache[$table])) {
            self::$__columns_cache[$table] = Schema::hasTable($table) ? Schema::getColumnListing($table) : [];
        }
        return self::$__columns_cache[$table];
    }

    protected function columnsForRelation(string $name): array
    {
        if (isset(self::$__relation_columns_cache[$name])) {
            return self::$__relation_columns_cache[$name];
        }
        $map = [
            'tree'  => 'trees',
            'bush'  => 'bushes',
            'hedge' => 'hedges',
            'flower'=> 'flowers',
            'plot'  => 'plots',
            'infrastructureType' => 'infrastructure_types',
        ];
        $table = $map[$name] ?? null;
        return self::$__relation_columns_cache[$name] = $table && Schema::hasTable($table)
            ? Schema::getColumnListing($table)
            : [];
    }

    protected function intersectByColumns(array $data, array $columns): array
    {
        $out = [];
        foreach ($data as $k => $v) {
            if (in_array($k, $columns, true) && !is_array($v) && !is_object($v)) $out[$k] = $v;
        }
        return $out;
    }
}
