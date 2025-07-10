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
        if (!isset($filters['green']) && !isset($filters['infrastructure'])) {
            return collect();
        }

        $query = Marker::query()
            ->with(['icon', 'green:id,quality_state', 'infrastructure', 'green.species.genus.family'])
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId);

        $query->where(function ($q) use ($filters) {
            if (!isset($filters['green'])) {
                $q->where('type', 'infrastructure');
            }
            if (!empty($filters['green'])) {
                $this->greenFilter->apply($q, $filters['green']);
            }

            if (!isset($filters['infrastructure'])) {
                $q->whereIn('type', GreenType::Values());
            }
            if (!empty($filters['infrastructure'])) {
                $this->infrastructureFilter->apply($q, $filters['infrastructure']);
            }
        });

        return $query->get();
    }
}
