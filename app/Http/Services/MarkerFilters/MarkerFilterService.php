<?php
namespace App\Http\Services\MarkerFilters;

use App\Models\Marker;

class MarkerFilterService
{
    public function __construct(
        protected MarkerGreenFilterService $greenFilter,
        protected MarkerInfrastructureFilterService $infrastructureFilter
    ) {}

    public function filter($parkId, $filters)
    {
        $query = Marker::query()
            ->with(['icon', 'green:id,quality_state', 'infrastructure', 'green.species.genus.family'])
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId);

        if (!isset($filters['green']) && !isset($filters['infrastructure'])) {
            return collect();
        }

        $query->where(function ($q) use ($filters) {
            if (!empty($filters['green'])) {
                $this->greenFilter->apply($q, $filters['green']);
            }

            if (!empty($filters['infrastructure'])) {
                $this->infrastructureFilter->apply($q, $filters['infrastructure']);
            }
        });

        return $query->get();
    }
}
