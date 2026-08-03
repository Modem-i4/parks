<?php

namespace App\Http\Services;

use App\Enums\GreenType;
use App\Enums\TagType;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ValidateMarkerService
{
    public function __construct(
        private SanitizeMarkerDescriptionService $descriptionSanitizer,
    ) {}

    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'park_id' => ['sometimes', 'required', 'exists:parks,id'],
            'type' => ['sometimes', Rule::in(TagType::values())],
            'coordinates' => ['sometimes', 'required', 'array', 
            function ($attribute, $value, $fail) {
                if (!$this->validCoordinates($value)) {
                    $fail('Поле '.$attribute.' повинно містити координати у форматі [lng, lat] або вкладений масив таких координат.');
                }
            }],
            'description' => ['sometimes', 'nullable', 'string'],
            'tags' => ['sometimes', 'array'],
            'tags.*.id' => ['required', 'integer', 'exists:tags,id'],

            'green.inventory_number' => ['sometimes', 'nullable', 'string'],
            'green.plot_id' => ['sometimes', 'nullable', 'exists:plots,id'],
            'green.subplot_id' => ['sometimes', 'nullable', 'exists:subplots,id'],
            'green.species_id' => ['sometimes', 'nullable', 'exists:species,id'],
            'green.planting_date' => ['sometimes', 'nullable', 'date'],
            'green.green_state' => ['sometimes', 'string'],
            'green.green_state_note' => ['sometimes', 'nullable', 'string'],
            'green.species.family_type' => ['sometimes', Rule::in(array_column(GreenType::cases(), 'value'))],

            'green.tree.height_m' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.trunk_circumference_cm' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.tilt_degree' => ['sometimes', 'nullable', 'numeric'],
            'green.tree.crown_condition_percent' => ['sometimes', 'nullable', 'numeric'],

            'green.bush.quantity' => ['sometimes', 'nullable', 'integer'],

            'green.hedge.length_m' => ['sometimes', 'nullable', 'numeric'],
            'green.hedge.hedge_row_id' => ['sometimes', 'nullable', 'exists:hedge_rows,id'],
            'green.hedge.hedge_shape_id' => ['sometimes', 'nullable', 'exists:hedge_shapes,id'],

            'infrastructure.name' => ['sometimes', 'required', 'string'],
            'infrastructure.infrastructure_type_id' => ['sometimes', 'required', 'exists:infrastructure_type,id'],
        ]);

        $validator->after(function ($v) use ($data) {
            if (data_get($data, 'green.plot_id') && !data_get($data, 'green.subplot_id'))
                $v->errors()->add('green.subplot_id', 'Якщо обраний виділ, потрібно обрати і ділянку.');
        });

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $validated = $validator->validated();
        if (array_key_exists('description', $validated)) {
            $validated['description'] = $this->descriptionSanitizer->sanitize($validated['description']);
        }

        return $validated;
    }

    private function validCoordinates(mixed $coordinates): bool
    {
        return is_array($coordinates) && $coordinates !== [] && (
            (count($coordinates) === 2 && is_numeric($coordinates[0] ?? null) && is_numeric($coordinates[1] ?? null))
            || collect($coordinates)->every(fn ($item) => $this->validCoordinates($item))
        );
    }

}
