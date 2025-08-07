<?php

namespace App\Http\Services;

use App\Enums\GreenType;
use App\Enums\TagType;
use App\Models\Green;
use App\Models\Marker;
use Illuminate\Support\Facades\DB;

class UpdateMarkerService
{
    public function handle(Marker $marker, array $data): void
    {
        DB::transaction(function () use ($marker, $data) {
            $fillableMarkerFields = ['park_id', 'type', 'coordinates', 'description'];
            $marker->fill(array_intersect_key($data, array_flip($fillableMarkerFields)));

            $typeChanged = $marker->isDirty('type');

            if ($marker->isDirty()) {
                $marker->save();
            }

            if (array_key_exists('tags', $data)) {
                $tagIds = collect($data['tags'])->pluck('id')->toArray();
                $marker->tags()->sync($tagIds);
            }

            if ($typeChanged) {
                $marker->green?->delete();
                $marker->infrastructure?->delete();
            }

            if (TagType::isGreenType($marker->type)) {
                $green = $marker->green ?? $marker->green()->firstOrNew();
                $green->fill(array_intersect_key($data['green'], array_flip([
                    'inventory_number','plot_id', 'species_id', 'planting_date', 'green_state', 'green_state_note'
                ])));

                if (!$green->exists || $green->isDirty()) {
                    $green->save();
                }

                $this->updateGreenSubtype($green, $marker->type, $data['green']);
            }

            if ($marker->type === TagType::INFRASTRUCTURE->value) {
                $infra = $marker->infrastructure ?? $marker->infrastructure()->firstOrNew();
                $infra->fill(array_intersect_key($data['infrastructure'], array_flip([
                    'name', 'infrastructure_type_id'
                ])));
                
                if (!$infra->exists || $infra->isDirty()) {
                    $infra->save();
                }
            }
        });
    }

    protected function updateGreenSubtype(Green $green, string $type, array $greenData): void
    {
        $this->deleteGreenSubtypes($green, except: $type);

        match ($type) {
            GreenType::TREE->value => $green->tree()->updateOrCreate([], array_intersect_key(
                $greenData['tree'] ?? [],
                array_flip(['height_m', 'trunk_diameter_cm', 'trunk_circumference_cm', 'tilt_degree', 'crown_condition_percent'])
            )),
            GreenType::BUSH->value => $green->bush()->updateOrCreate([], array_intersect_key(
                $greenData['bush'] ?? [],
                array_flip(['quantity'])
            )),
            GreenType::HEDGE->value => $green->hedge()->updateOrCreate([], array_intersect_key(
                $greenData['hedge'] ?? [],
                array_flip(['length_m', 'hedge_row_id', 'hedge_shape_id'])
            )),
            GreenType::FLOWER->value => $green->flower()->updateOrCreate([], []),
            default => null,
        };
    }

    protected function deleteGreenSubtypes(Green $green, ?string $except = null): void
    {
        foreach (GreenType::values() as $type) {
            if ($type !== $except) {
                $green->{strtolower($type)}()?->delete();
            }
        }
    }
}
