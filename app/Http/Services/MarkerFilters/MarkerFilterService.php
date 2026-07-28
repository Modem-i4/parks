<?php
namespace App\Http\Services\MarkerFilters;

use App\Enums\GreenType;
use App\Models\Marker;

class MarkerFilterService
{
    public function __construct(
        protected MarkerGreenFilterService $greenFilter,
        protected MarkerInfrastructureFilterService $infrastructureFilter
    ) {}

    public function filter($parkId, $filters)
    {
        if (!$this->hasMarkerIdsFilter($filters)
            && !isset($filters['green'])
            && !isset($filters['infrastructure'])) {
            return collect();
        }

        $query = Marker::query()
            ->with(['icon', 'green:id,green_state,species_id,inventory_number', 'infrastructure:id,name,infrastructure_type_id', 'green.species:id,name_ukr'])
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId);

        if ($this->applyMarkerIdsFilter($query, $filters)) {
            return $query->get();
        }

        $this->applyMarkerFilters($query, $filters);

        return $query->get();
    }

    public function countByParks($filters)
    {
        if (!$this->hasMarkerIdsFilter($filters)
            && !isset($filters['green'])
            && !isset($filters['infrastructure'])) {
            return collect();
        }

        $query = Marker::query();

        if (!$this->applyMarkerIdsFilter($query, $filters)) {
            $this->applyParkFilters($query, $filters);
            $this->applyMarkerFilters($query, $filters);
        }

        return $query
            ->select('park_id')
            ->selectRaw('COUNT(*) as markers_count')
            ->groupBy('park_id')
            ->pluck('markers_count', 'park_id');
    }

    public function filteredIds($filters): array
    {
        if (!$this->hasMarkerIdsFilter($filters)
            && !isset($filters['green'])
            && !isset($filters['infrastructure'])) {
            return [];
        }

        $query = Marker::query();

        if (!$this->applyMarkerIdsFilter($query, $filters)) {
            $this->applyParkFilters($query, $filters);
            $this->applyMarkerFilters($query, $filters);
        }

        return $query->pluck('id')->all();
    }

    private function hasMarkerIdsFilter($filters): bool
    {
        return is_array($filters) && array_key_exists('marker_ids', $filters);
    }

    private function applyMarkerIdsFilter($query, $filters): bool
    {
        if (!$this->hasMarkerIdsFilter($filters)) {
            return false;
        }

        $query->whereIn('id', array_filter(
            array_map('intval', (array) $filters['marker_ids']),
            fn (int $id) => $id > 0
        ));

        return true;
    }

    private function applyMarkerFilters($query, $filters): void
    {
        $query->where(function ($q) use ($filters) {
            if (!empty($filters['green'])) {
                $q->orWhere(function ($sub) use ($filters) {
                    $sub->whereIn('type', GreenType::values());
                    $this->greenFilter->apply($sub, $filters['green']);
                });
            }
            if (!empty($filters['infrastructure'])) {
                $this->infrastructureFilter->apply($q, $filters['infrastructure']);
            }

            if (isset($filters['green']) && empty($filters['green'])) {
                $q->orWhereIn('type', GreenType::values());
            }
            if (isset($filters['infrastructure']) && empty($filters['infrastructure'])) {
                $q->orWhere('type', 'infrastructure');
            }
        });
    }

    private function applyParkFilters($query, array $filters): void
    {
        $parkFilters = $filters['park'] ?? [];
        if (empty($parkFilters) || !is_array($parkFilters)) {
            return;
        }

        $directParkIds = array_is_list($parkFilters) ? $parkFilters : [];
        $legacyParkIds = array_values(array_filter(array_map(
            'intval',
            array_merge($directParkIds, $parkFilters['parks'] ?? [])
        )));
        $parkEntries = array_filter(
            $parkFilters,
            fn ($value, $key) => is_numeric($key) && is_array($value),
            ARRAY_FILTER_USE_BOTH
        );
        $legacyParkIds = array_values(array_diff($legacyParkIds, array_map('intval', array_keys($parkEntries))));

        if (empty($parkEntries)) {
            if (!empty($legacyParkIds)) {
                $query->whereIn('park_id', $legacyParkIds);
            }
            return;
        }

        $query->where(function ($parkQuery) use ($parkEntries, $legacyParkIds) {
            if (!empty($legacyParkIds)) {
                $parkQuery->orWhereIn('park_id', $legacyParkIds);
            }

            foreach ($parkEntries as $parkId => $data) {
                $parkQuery->orWhere(function ($singleParkQuery) use ($parkId, $data) {
                    $singleParkQuery->where('park_id', (int) $parkId);

                    if (!empty($data['plots']) && is_array($data['plots'])) {
                        $singleParkQuery->whereHas('green', function ($greenQuery) use ($data) {
                            $this->greenFilter->applyPlots($greenQuery, $data['plots']);
                        });
                    }
                });
            }
        });
    }
}
