<?php
namespace App\Http\Services\MarkerFilters;

class MarkerGreenFilterService {

    private array $typeMap = [
        'trees' => 'tree',
        'bushes' => 'bush',
        'hedges' => 'hedge',
        'flowers' => 'flower',
        'tags' => 'tags'
    ];

    public function apply($query, array $filters): void
    {
        $types = $this->extractTypes($filters);

        $query->whereIn('type', $types);

        $this->applyGeneral($query, $filters);

        if (!empty($filters['general']['common_tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tag_id', $filters['general']['common_tags']);
            });
        }

        $query->where(function ($q) use ($filters) {
            foreach ($this->typeMap as $slug => $type) {
                if (!empty($filters[$slug])) {
                    $q->orWhere(function ($q1) use ($filters, $slug, $type) {
                        $q1->where('type', $type);

                        if (!empty($filters[$slug]['tags'])) {
                            $q1->whereHas('tags', function ($t) use ($filters, $slug) {
                                $t->whereIn('tag_id', $filters[$slug]['tags']);
                            });
                        }

                        $this->applySpecific($q1, $filters[$slug], $type);
                    });
                }
            }
        });
    }

    private function extractTypes($filters): array
    {
        $types = [];
        foreach ($this->typeMap as $slug => $type) {
            if (isset($filters[$slug])) {
                $types[] = $type;
            }
        }

        return !empty($types) ? $types : ['tree', 'bush', 'hedge', 'flower'];
    }

    private function applyGeneral($query, $filters): void
    {
        $query->whereHas('green', function ($q) use ($filters) {
            if (!empty($filters['general']['quality_state'])) {
                $q->whereIn('quality_state', $filters['general']['quality_state']);
            }

            if (!empty($filters['general']['age_range'])) {
                $q->whereBetween('planting_date', [
                    now()->subYears($filters['general']['age_range'][1]),
                    now()->subYears($filters['general']['age_range'][0]),
                ]);
            }

            if (!empty($filters['general']['recommendations'])) {
                $q->whereHas('greenWorksHistory.recommendations', function ($s) use ($filters) {
                    $s->whereIn('name', $filters['general']['recommendations']);
                });
            }
        });
    }

    private function applySpecific($query, $filters, $type): void
    {
        $query->whereHas("green.$type", function ($q) use ($filters, $type) {
            foreach ($filters as $key => $value) {
                if (in_array($key, ['tags', 'species']) || empty($value)) {
                    continue;
                }

                $column = match ($key) {
                    'height_m' => 'height_m',
                    'trunk_diameter_cm' => 'trunk_diameter_cm',
                    'trunk_circumference_cm' => 'trunk_circumference_cm',
                    'tilt_degree' => 'tilt_degree',
                    'crown_condition_percent' => 'crown_condition_percent',
                    'quantity' => 'quantity',
                    'length' => 'length_m',
                    'type_row' => 'hedge_row',
                    'type_shape' => 'hedge_shape',
                    default => null,
                };

                if ($column) {
                    if (is_array($value)) {
                        $q->whereBetween($column, $value);
                    } else {
                        $q->whereIn($column, (array)$value);
                    }
                }
            }
                            
            if (!empty($filters['taxonomy'])) {
                $q->whereHas('green', function ($gq) use ($filters) {
                    $gq->where(function ($taxQuery) use ($filters) {
                        foreach ($filters['taxonomy'] as $tax) {
                            if (isset($tax['family'])) {
                                $taxQuery->orWhereHas("species.genus.family", function ($f) use ($tax) {
                                    $f->where('id', $tax['family']);
                                });
                            }

                            if (isset($tax['genus'])) {
                                $taxQuery->orWhereHas("species.genus", function ($g) use ($tax) {
                                    $g->where('id', $tax['genus']);
                                });
                            }

                            if (isset($tax['species'])) {
                                $taxQuery->orWhereHas("species", function ($s) use ($tax) {
                                    $s->where('id', $tax['species']);
                                });
                            }
                        }
                    });
                });
            }
        });
    }
}
