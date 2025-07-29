<?php
namespace App\Http\Services\MarkerFilters;

use App\Enums\QualityState;
use App\Enums\UserRole;
use App\Models\Recommendation;
use App\Models\Species;
use App\Models\HedgeRow;
use App\Models\HedgeShape;
use App\Models\InfrastructureType;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class MarkerFilterConfigService {
    public function get($mode = 'infrastructure'): array
    {        
        $config = $this->getDefaultFilters();
        $config = $this->filterConfigByMode($config, $mode);
        $userRole = Auth::check()
            ? Auth::user()->role
            : UserRole::UNAUTHORIZED;
        $config = $this->filterConfigByRole($config, $userRole);
        return $config;
    }

    protected function getDefaultFilters(): array
    {
        // Get dynamic options
        $recommendations = Recommendation::select('id', 'name')->get()->toArray();
        $species = Species::with('genus.family')->select('id', 'name_ukr')->get()->groupBy('type')->toArray();
        $hedgeTypeRows = HedgeRow::pluck('name')->toArray();
        $hedgeTypeShapes = HedgeShape::pluck('name')->toArray();
        $infrastructureTypes = InfrastructureType::with('icon')
            ->get()->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'icon' => $type->icon?->file_path,
                ];
            })->toArray();

        $tags = Tag::select('id', 'name', 'type')->get()->groupBy('type')->map->toArray();
        return [ 
            'infrastructure' => [
                'name' => 'Інфраструктура',
                'slug' => 'infrastructure',
                'type' => 'group',
                'checked' => true,
                'children' => [
                    [
                        'name' => 'Типи інфраструктури',
                        'slug' => 'types',
                        'type' => 'infrastructureSelect',
                        'options' => $infrastructureTypes,
                    ],
                    [
                        'name' => 'Теги',
                        'slug' => 'tags',
                        'type' => 'multiselect',
                        'options' => $tags['infrastructure'] ?? [],
                    ],
                ],
            ],
            'green' =>[
                'name' => 'Зелені насадження',
                'slug' => 'green',
                'type' => 'group',
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
                                'type' => 'stateSelect',
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
                                'options' => $tags['all'] ?? [],
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
                                'options' => $tags['tree'] ?? [],
                            ],
                            [
                                'name' => 'Класифікація дерев',
                                'slug' => 'species',
                                'type' => 'taxonomy',
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
                                'options' => $tags['bush'] ?? [],
                            ],
                            [
                                'name' => 'Класифікація кущів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
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
                                'options' => $tags['hedge'] ?? [],
                            ],
                            [
                                'name' => 'Класифікація живоплотів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
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
                                'options' => $tags['flower'] ?? [],
                            ],
                            [
                                'name' => 'Класифікація квітів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
                            ],
                        ],
                    ],
                ],
            ],
        ];
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

    protected function filterConfigByMode(array $config, string $mode): array
    {
        $config[$mode]['open'] = true;

        if ($mode === 'infrastructure') {
            return [
                'infrastructure' => $config['infrastructure'],
                'green' => $config['green'],
            ];
        } else {
            return [
                'green' => $config['green'],
                'infrastructure' => $config['infrastructure'],
            ];
        }
    }
}
