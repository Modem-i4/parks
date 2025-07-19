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
            ->with(['icon', 'green:id,quality_state', 'infrastructure:id,infrastructure_type_id', 'green.species.genus.family'])
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId);

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
        return $query->get();
    }
}
