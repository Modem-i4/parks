<?php

namespace App\Http\Services;

use App\Enums\GreenType;
use App\Enums\TagType;
use App\Models\Green;
use App\Models\Marker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UpdateMarkerService
{
    public function handle(Marker $marker, array $rawData): void
    {
        $data = $this->validate($rawData);

        DB::transaction(function () use ($marker, $data) {
            $fillableMarkerFields = ['park_id', 'plot_id', 'type', 'coordinates', 'description'];
            $marker->fill(array_intersect_key($data, array_flip($fillableMarkerFields)));

            $typeChanged = $marker->isDirty('type');

            if ($marker->isDirty()) {
                $marker->save();
            }

            if (array_key_exists('tag_ids', $data)) {
                $marker->tags()->sync($data['tag_ids']);
            }

            if ($typeChanged) {
                $marker->green?->delete();
                $marker->infrastructure?->delete();
            }

            if (TagType::isGreenType($marker->type) && isset($data['green'])) {
                $green = $marker->green ?? $marker->green()->firstOrNew();
                $green->fill(array_intersect_key($data['green'], array_flip([
                    'inventory_number', 'species_id', 'planting_date', 'quality_state', 'quality_state_note'
                ])));

                if (!$green->exists || $green->isDirty()) {
                    $green->save();
                }

                $this->updateGreenSubtype($green, $marker->type, $data['green']);
            }

            if ($marker->type === TagType::INFRASTRUCTURE->value && isset($data['infrastructure'])) {
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
                array_flip(['length_m', 'hedge_type_row', 'hedge_type_shape'])
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

    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'park_id' => ['sometimes', 'exists:parks,id'],
            'plot_id' => ['sometimes', 'nullable', 'exists:plots,id'],
            'type' => ['sometimes', Rule::in(TagType::values())],
            'coordinates' => ['sometimes', 'array'],
            'coordinates.0' => ['sometimes', 'numeric'],
            'coordinates.1' => ['sometimes', 'numeric'],
            'description' => ['sometimes', 'nullable', 'string'],
            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],

            'green.inventory_number' => ['sometimes', 'string'],
            'green.species_id' => ['sometimes', 'exists:species,id'],
            'green.planting_date' => ['sometimes', 'nullable', 'date'],
            'green.quality_state' => ['sometimes', 'nullable', 'string'],
            'green.quality_state_note' => ['sometimes', 'nullable', 'string'],
            'green.species.family_type' => ['sometimes', Rule::in(array_column(GreenType::cases(), 'value'))],

            'green.tree.height_m' => ['sometimes', 'numeric'],
            'green.tree.trunk_diameter_cm' => ['sometimes', 'numeric'],
            'green.tree.trunk_circumference_cm' => ['sometimes', 'numeric'],
            'green.tree.tilt_degree' => ['sometimes', 'numeric'],
            'green.tree.crown_condition_percent' => ['sometimes', 'numeric'],

            'green.bush.quantity' => ['sometimes', 'integer'],

            'green.hedge.length_m' => ['sometimes', 'numeric'],
            'green.hedge.hedge_type_row' => ['sometimes', 'string'],
            'green.hedge.hedge_type_shape' => ['sometimes', 'string'],

            'infrastructure.name' => ['sometimes', 'string'],
            'infrastructure.infrastructure_type_id' => ['sometimes', 'exists:infrastructure_type,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}
