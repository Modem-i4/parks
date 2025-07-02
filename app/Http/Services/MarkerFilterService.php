<?php

namespace App\Http\Services;

use App\Enums\QualityState;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use App\Models\Recommendation;
use App\Models\Species;
use App\Models\HedgeTypeRow;
use App\Models\HedgeTypeShape;
use App\Models\Infrastructure;
use App\Models\Marker;
use App\Models\Park;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class MarkerFilterService
{
    public function getFiltersConfig()
    {
        // Get dynamic options
        $recommendations = Recommendation::pluck('name')->toArray();
        $species = Species::select('id', 'name_ukr', 'type')->get()->groupBy('type')->toArray();
        $hedgeTypeRows = HedgeTypeRow::pluck('name')->toArray();
        $hedgeTypeShapes = HedgeTypeShape::pluck('name')->toArray();
        $infrastructureTypes = Infrastructure::distinct('name')->pluck('name')->toArray();
        $tags = Tag::select('name', 'type')->get()->groupBy('type')->toArray();
        
        $config = [ 
            'green' =>[
                'name' => 'Зелені насадження',
                'slug' => 'green',
                'type' => 'group',
                'open' => true,
                'checked' => true,
                'children' => [
                    [
                        'name' => 'Загальні фільтри',
                        'slug' => 'general',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Стан',
                                'slug' => 'quality_state',
                                'type' => 'multiselect',
                                'options' => QualityState::values(),
                            ],
                            [
                                'name' => 'Вік – від і до',
                                'slug' => 'age_range',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 150,
                            ],
                            [
                                'name' => 'Рекомендації',
                                'slug' => 'recommendations',
                                'type' => 'multiselect',
                                'options' => $recommendations,
                                'role' => 'worker'
                            ],
                            [
                                'name' => 'Спільні теги',
                                'slug' => 'common_tags',
                                'type' => 'multiselect',
                                'options' => isset($tags['all']) ? array_column($tags['all'], 'name') : [],
                            ],
                        ],
                    ],
                    [
                        'name' => 'Дерева',
                        'slug' => 'trees',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => isset($tags['tree']) ? array_column($tags['tree'], 'name') : [],
                            ],
                            [
                                'name' => 'Види дерев',
                                'slug' => 'species',
                                'type' => 'multiselect',
                                'options' => isset($species['tree']) ? array_column($species['tree'], 'name_ukr') : [],
                            ],
                            [
                                'name' => 'Висота (м)',
                                'slug' => 'height_m',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 50,
                            ],
                            [
                                'name' => 'Діаметр стовбура (см)',
                                'slug' => 'trunk_diameter_cm',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 200,
                            ],
                            [
                                'name' => 'Окружність стовбура (см)',
                                'slug' => 'trunk_circumference_cm',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 800,
                            ],
                            [
                                'name' => 'Нахил (°)',
                                'slug' => 'tilt_degree',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 45,
                            ],
                            [
                                'name' => 'Стан крони (%)',
                                'slug' => 'crown_condition_percent',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 100,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Кущі',
                        'slug' => 'bushes',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => isset($tags['bush']) ? array_column($tags['bush'], 'name') : [],
                            ],
                            [
                                'name' => 'Види кущів',
                                'slug' => 'species',
                                'type' => 'multiselect',
                                'options' => isset($species['bush']) ? array_column($species['bush'], 'name_ukr') : [],
                            ],
                            [
                                'name' => 'Кількість',
                                'slug' => 'quantity',
                                'type' => 'slider',
                                'min' => 1,
                                'max' => 20,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Живоплоти',
                        'slug' => 'hedges',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => isset($tags['hedge']) ? array_column($tags['hedge'], 'name') : [],
                            ],
                            [
                                'name' => 'Види живоплотів',
                                'slug' => 'species',
                                'type' => 'multiselect',
                                'options' => isset($species['hedge']) ? array_column($species['hedge'], 'name_ukr') : [],
                            ],
                            [
                                'name' => 'Довжина (м)',
                                'slug' => 'length',
                                'type' => 'slider',
                                'min' => 0,
                                'max' => 500,
                            ],
                            [
                                'name' => 'Тип ряду',
                                'slug' => 'type_row',
                                'type' => 'multiselect',
                                'options' => $hedgeTypeRows,
                            ],
                            [
                                'name' => 'Форма',
                                'slug' => 'type_shape',
                                'type' => 'multiselect',
                                'options' => $hedgeTypeShapes,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Квіти',
                        'slug' => 'flowers',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => isset($tags['flower']) ? array_column($tags['flower'], 'name') : [],
                            ],
                            [
                                'name' => 'Види квітів',
                                'slug' => 'species',
                                'type' => 'multiselect',
                                'options' => isset($species['flower']) ? array_column($species['flower'], 'name_ukr') : [],
                            ],
                        ],
                    ],
                ],
            ],
            'infrastructure' => [
                'name' => 'Інфраструктура',
                'slug' => 'infrastructure',
                'type' => 'group',
                'checked' => true,
                'children' => [
                    [
                        'name' => 'Типи інфраструктури',
                        'slug' => 'types',
                        'type' => 'multiselect',
                        'options' => $infrastructureTypes,
                    ],
                    [
                        'name' => 'Теги',
                        'slug' => 'tags',
                        'type' => 'multiselect',
                        'options' => isset($tags['infrastructure']) ? array_column($tags['infrastructure'], 'name') : [],
                    ],
                ],
            ],
        ];
        $userRole = Auth::check()
            ? Auth::user()->role
            : UserRole::UNAUTHORIZED;
        $config = $this->filterConfigByRole($config, $userRole);
        return $config;
    }

    protected function filterConfigByRole(array $config, $userRole): array
    {
        foreach ($config as $key => &$node) {
            if (isset($node['role'])) {
                $requiredRole = UserRole::fromString($node['role']);

                if ($userRole->level() < $requiredRole->level()) {
                    unset($config[$key]);
                    continue;
                }
            }

            if (isset($node['children']) && is_array($node['children'])) {
                $node['children'] = $this->filterConfigByRole($node['children'], $userRole);
            }
        }

        return array_values($config);
    }


    public function filter($parkId, $filters)
    {
        $query = Marker::query()
            ->with(['icon', 'green:id,quality_state', 'infrastructure'])
            ->select('id', 'coordinates', 'description', 'type')
            ->where('park_id', $parkId);
        if(isset($filters)) {
            if(!isset($filters['green']) && !isset($filters['infrastructure'])) {
                return collect();
            }
            if (!isset($filters['green'])) {
                $query->where('type', 'infrastructure');
            } elseif (!empty($filters['green'])) {
                $this->applyGreenFilters($query, $filters['green']);
            }
            if (!isset($filters['infrastructure'])) {
                $query->where('type', '<>', 'infrastructure');
            } elseif (!empty($filters['infrastructure'])) {
                $this->applyInfrastructureFilters($query, $filters['infrastructure']);
            }
        }

        return $query->get();
    }


    private function applyGreenFilters($query, $green_filters)
    {
        $types = array_filter([
            !empty($green_filters['trees']) ? 'tree' : null,
            !empty($green_filters['bushes']) ? 'bush' : null,
            !empty($green_filters['hedges']) ? 'hedge' : null,
            !empty($green_filters['flowers']) ? 'flower' : null,
        ]);

        if (!empty($types)) {
            $query->whereIn('type', $types);
        }

        // General filters (AND)
        if (!empty($green_filters['general']['quality_state'])) {
            $query->whereHas('green', function ($q) use ($green_filters) {
                $q->whereIn('quality_state', $green_filters['general']['quality_state']);
            });
        }

        if (!empty($green_filters['general']['age'])) {
            $query->whereHas('green', function ($q) use ($green_filters) {
                $q->whereBetween('planting_date', [
                    now()->subYears($green_filters['general']['age'][1]),
                    now()->subYears($green_filters['general']['age'][0]),
                ]);
            });
        }

        if (!empty($green_filters['general']['recommendations'])) {
            $query->whereHas('green.greenWorksHistory.recommendations', function ($q) use ($green_filters) {
                $q->whereIn('name', $green_filters['general']['recommendations']);
            });
        }

        // Specific filters (OR)
        $query->where(function ($q) use ($green_filters) {

            // trees
            if (!empty($green_filters['trees'])) {
                $q->orWhereHas('green.tree', function ($sub) use ($green_filters) {
                    if (!empty($green_filters['trees']['height_m'])) {
                        $sub->whereBetween('height_m', $green_filters['trees']['height_m']);
                    }
                    if (!empty($green_filters['trees']['trunk_diameter_cm'])) {
                        $sub->whereBetween('trunk_diameter_cm', $green_filters['trees']['trunk_diameter_cm']);
                    }
                    if (!empty($green_filters['trees']['trunk_circumference_cm'])) {
                        $sub->whereBetween('trunk_circumference_cm', $green_filters['trees']['trunk_circumference_cm']);
                    }
                    if (!empty($green_filters['trees']['tilt_degree'])) {
                        $sub->whereBetween('tilt_degree', $green_filters['trees']['tilt_degree']);
                    }
                    if (!empty($green_filters['trees']['crown_condition_percent'])) {
                        $sub->whereBetween('crown_condition_percent', $green_filters['trees']['crown_condition_percent']);
                    }
                    if (!empty($green_filters['trees']['species'])) {
                        $sub->whereHas('species', function ($s) use ($green_filters) {
                            $s->whereIn('name_ukr', $green_filters['trees']['species']);
                        });
                    }
                });
            }

            // bushes
            if (!empty($green_filters['bushes'])) {
                $q->orWhereHas('green.bush', function ($sub) use ($green_filters) {
                    if (!empty($green_filters['bushes']['quantity'])) {
                        $sub->whereBetween('quantity', $green_filters['bushes']['quantity']);
                    }
                    if (!empty($green_filters['bushes']['species'])) {
                        $sub->whereHas('species', function ($s) use ($green_filters) {
                            $s->whereIn('name_ukr', $green_filters['bushes']['species']);
                        });
                    }
                });
            }

            // hedges
            if (!empty($green_filters['hedges'])) {
                $q->orWhereHas('green.hedge', function ($sub) use ($green_filters) {
                    if (!empty($green_filters['hedges']['length'])) {
                        $sub->whereBetween('length_m', $green_filters['hedges']['length']);
                    }
                    if (!empty($green_filters['hedges']['type_row'])) {
                        $sub->whereIn('hedge_type_row', $green_filters['hedges']['type_row']);
                    }
                    if (!empty($green_filters['hedges']['type_shape'])) {
                        $sub->whereIn('hedge_type_shape', $green_filters['hedges']['type_shape']);
                    }
                    if (!empty($green_filters['hedges']['species'])) {
                        $sub->whereHas('species', function ($s) use ($green_filters) {
                            $s->whereIn('name_ukr', $green_filters['hedges']['species']);
                        });
                    }
                });
            }

            // flowers
            if (!empty($green_filters['flowers'])) {
                $q->orWhereHas('green.flower', function ($sub) use ($green_filters) {
                    if (!empty($green_filters['flowers']['species'])) {
                        $sub->whereHas('species', function ($s) use ($green_filters) {
                            $s->whereIn('name_ukr', $green_filters['flowers']['species']);
                        });
                    }
                });
            }
        });
    }
    private function applyInfrastructureFilters($query, $infra_filters)
    {
        $query->orWhere(function ($q) use ($infra_filters) {
            $q->where('type', 'infrastructure');

            if (!empty($infra_filters['type'])) {
                $q->whereHas('infrastructure', function ($sub) use ($infra_filters) {
                    $sub->whereIn('name', $infra_filters['type']);
                });
            }
        });
    }


}
