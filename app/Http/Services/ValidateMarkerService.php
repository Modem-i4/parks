<?php

namespace App\Http\Services;

use App\Enums\GreenType;
use App\Enums\TagType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidateMarkerService
{
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
            'tags' => ['sometimes', 'array'],
            'tags.*.id' => ['required', 'integer', 'exists:tags,id'],

            'green.inventory_number' => ['sometimes', 'nullable', 'string'],
            'green.species_id' => ['sometimes', 'nullable', 'exists:species,id'],
            'green.planting_date' => ['sometimes', 'nullable', 'date'],
            'green.quality_state' => ['sometimes', 'nullable', 'string'],
            'green.quality_state_note' => ['sometimes', 'nullable', 'string'],
            'green.species.family_type' => ['sometimes', Rule::in(array_column(GreenType::cases(), 'value'))],

            'green.tree.height_m' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.trunk_diameter_cm' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.trunk_circumference_cm' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.tilt_degree' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.crown_condition_percent' => ['sometimes', 'nullable', 'numeric'],

            'green.bush.quantity' => ['sometimes', 'nullable', 'integer'],

            'green.hedge.length_m' => ['sometimes', 'nullable', 'numeric'],
            'green.hedge.hedge_row' => ['sometimes', 'nullable', 'string'],
            'green.hedge.hedge_shape' => ['sometimes', 'nullable', 'string'],

            'infrastructure.name' => ['sometimes', 'nullable', 'string'],
            'infrastructure.infrastructure_type_id' => ['sometimes', 'nullable', 'exists:infrastructure_type,id'],
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }
}