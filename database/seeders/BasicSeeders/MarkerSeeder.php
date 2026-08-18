<?php

namespace Database\Seeders\BasicSeeders;

use App\Models\{
    Park, Plot, Subplot, Marker, Green,
    Tree, Bush, Hedge, Flower,
    Species, Tag, Recommendation, Work,
    HedgeRow, HedgeShape,
    MediaLibrary, Media
};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MarkerSeeder extends Seeder
{
    private array $parkIds = [];
    private array $speciesMap = [];
    private array $tagMap = [];
    private array $recMap = [];
    private array $rowMap = [];
    private array $shapeMap = [];
    private array $inventoryNumberMap = [];
    private array $inventoryTagMap = [];

    private array $plots = [];
    private array $subplots = [];

    public function run(): void
    {
        $data = include database_path('data/Markers.php');
        $this->inventoryNumberMap = include database_path('data/GreenInventoryNumbers.php');
        $this->inventoryTagMap = include database_path('data/TreeInventoryTags.php');

        $this->parkIds = Park::query()->whereNotNull('slug')->pluck('id', 'slug')->all();

        foreach (Species::query()->get(['id', 'name_lat', 'name_ukr']) as $s) {
            if ($s->name_lat) $this->speciesMap[$this->k($s->name_lat)] = $s->id;
            if ($s->name_ukr) $this->speciesMap[$this->k($s->name_ukr)] = $s->id;
        }

        $this->tagMap = Tag::query()->get(['id', 'name'])->mapWithKeys(fn($t) => [$this->k($t->name) => $t->id])->all();
        $this->recMap = Recommendation::query()->get(['id', 'name'])->mapWithKeys(fn($r) => [$this->k($r->name) => $r->id])->all();
        $this->rowMap = HedgeRow::query()->get(['id', 'name'])->mapWithKeys(fn($r) => [$this->k($r->name) => $r->id])->all();
        $this->shapeMap = HedgeShape::query()->get(['id', 'name'])->mapWithKeys(fn($r) => [$this->k($r->name) => $r->id])->all();

        DB::transaction(fn() => $this->seedMarkers($data));
    }

    public function seedMarkers($data): void
    {
        foreach ($data as $parkSlug => $byType) {
            $parkId = $this->parkIds[$parkSlug] ?? null;
            if (!$parkId) throw new \RuntimeException("Park not found by slug: {$parkSlug}");

            foreach ($byType as $type => $items) {
                foreach ($items as $item) {
                    $coords = $item['coordinates'] ?? null;
                    $props  = $item['props'] ?? [];
                    $tProps = $item[$type] ?? [];

                    $subplotId = null;
                    if (!empty($props['plot']) && !empty($props['subplot'])) {
                        $plotId = $this->getOrCreatePlotId($parkId, (string)$props['plot']);
                        $subplotId = $this->getOrCreateSubplotId($plotId, (string)$props['subplot']);
                    }

                    $speciesId = null;
                    if (!empty($props['species'])) {
                        $speciesId = $this->requireId($this->speciesMap, (string)$props['species'], 'Species');
                    }

                    $marker = Marker::create([
                        'park_id' => $parkId,
                        'type' => $type,
                        'coordinates' => $coords,
                        'description' => $props['description'] ?? null,
                    ]);

                    $oldInventoryNumber = (string) $props['inventory_number'];

                    $green = Green::create([
                        'id' => $marker->id,
                        'inventory_number' => (string) $this->inventoryNumberMap[$oldInventoryNumber],
                        'inventory_number_old' => $oldInventoryNumber,
                        'subplot_id' => $subplotId,
                        'species_id' => $speciesId,
                        'planting_date' => $props['planting_date'] ?? null,
                        'green_state' => $props['green_state'] ?? 'good',
                        'green_state_note' => $props['green_state_note'] ?? null,
                    ]);

                    if ($type === 'tree') {
                        Tree::create([
                            'id' => $green->id,
                            'inventory_tag' => $this->inventoryTagMap[$green->inventory_number] ?? null,
                            'height_m' => $tProps['height_m'] ?? null,
                            'trunk_circumference_cm' => $tProps['trunk_circumference_cm'] ?? null,
                            'tilt_degree' => $tProps['tilt_degree'] ?? null,
                            'crown_condition_percent' => $tProps['crown_condition_percent'] ?? null,
                            'area' => $tProps['area'] ?? null,
                        ]);
                    } elseif ($type === 'bush') {
                        Bush::create([
                            'id' => $green->id,
                            'quantity' => $tProps['quantity'] ?? null,
                            'area' => $tProps['area'] ?? null,
                        ]);
                    } elseif ($type === 'hedge') {
                        $payload = [
                            'id' => $green->id,
                            'length_m' => $tProps['length_m'] ?? null,
                            'area' => $tProps['area'] ?? null,
                        ];

                        if (Schema::hasColumn('hedges', 'hedge_row_id') && !empty($tProps['hedge_row'])) {
                            $payload['hedge_row_id'] = $this->requireId($this->rowMap, (string)$tProps['hedge_row'], 'HedgeRow');
                        }
                        if (Schema::hasColumn('hedges', 'hedge_shape_id') && !empty($tProps['hedge_shape'])) {
                            $payload['hedge_shape_id'] = $this->requireId($this->shapeMap, (string)$tProps['hedge_shape'], 'HedgeShape');
                        }

                        Hedge::create($payload);
                    } elseif ($type === 'flower') {
                        Flower::create(['id' => $green->id]);
                    }

                    foreach (($item['tags'] ?? []) as $name) {
                        $tagId = $this->requireId($this->tagMap, (string)$name, 'Tag');
                        DB::table('markers_tags')->insertOrIgnore(['marker_id' => $marker->id, 'tag_id' => $tagId]);
                    }

                    foreach (($item['recommendations'] ?? []) as $name) {
                        $recId = $this->requireId($this->recMap, (string)$name, 'Recommendation');
                        Work::create([
                            'green_id' => $green->id,
                            'recommendation_id' => $recId,
                            'recommendation_date' => now()->toDateString(),
                            'recommender_id' => null,
                            'execution_date' => null,
                            'executor_id' => null,
                            'notes' => null,
                        ]);
                    }

                    if (!empty($props['img'])) {
                        $ml = MediaLibrary::create([
                            'file_path' => (string)$props['img'],
                            'type' => 'image',
                        ]);

                        Media::create([
                            'media_library_id' => $ml->id,
                            'model_type' => Marker::class,
                            'model_id' => $marker->id,
                            'description' => null,
                            'order' => 0,
                        ]);
                    }
                }
            }
        }
    }

    private function getOrCreatePlotId(int $parkId, string $name): int
    {
        $key = $parkId . '|' . $this->k($name);
        if (isset($this->plots[$key])) return $this->plots[$key];

        $plot = Plot::firstOrCreate(
            ['park_id' => $parkId, 'name' => $name],
            ['coordinates' => [null, null]]
        );

        return $this->plots[$key] = $plot->id;
    }

    private function getOrCreateSubplotId(int $plotId, string $name): int
    {
        $key = $plotId . '|' . $this->k($name);
        if (isset($this->subplots[$key])) return $this->subplots[$key];

        $subplot = Subplot::firstOrCreate(['plot_id' => $plotId, 'name' => $name]);
        return $this->subplots[$key] = $subplot->id;
    }

    private function requireId(array $map, string $name, string $what): int
    {
        $key = $this->k($name);
        $id = $map[$key] ?? null;
        if (!$id) throw new \RuntimeException("Not found: {$what} by '{$name}'");
        return (int)$id;
    }

    private function k(?string $s): string
    {
        $s = (string)($s ?? '');
        $s = str_replace("\xC2\xA0", ' ', $s);
        $s = preg_replace('/\s+/u', ' ', trim($s));
        return mb_strtolower($s);
    }
}
