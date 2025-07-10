<?php
namespace App\Http\Services\MarkerFilters;

class MarkerInfrastructureFilterService {
    public function apply($query, array $filters): void
    {
        $query->orWhere(function ($q) use ($filters) {
            $q->where('type', 'infrastructure');

            if (!empty($filters['types'])) {
                $q->whereHas('infrastructure', function ($sub) use ($filters) {
                    $sub->whereIn('infrastructure_type_id', $filters['types']);
                });
            }

            if (!empty($filters['tags'])) {
                $q->whereHas('tags', function ($t) use ($filters) {
                    $t->whereIn('tag_id', $filters['tags']);
                });
            }
        });
    }
}
