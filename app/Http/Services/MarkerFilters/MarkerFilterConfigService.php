<?php
namespace App\Http\Services\MarkerFilters;

use App\Enums\GreenState;
use App\Enums\UserRole;
use App\Models\Park;
use App\Models\Recommendation;
use App\Models\Species;
use App\Models\HedgeRow;
use App\Models\HedgeShape;
use App\Models\InfrastructureType;
use App\Models\Tag;
use Illuminate\Support\Facades\Auth;

class MarkerFilterConfigService {
    public function get($mode = 'green', string $scope = 'local'): array
    {        
        $config = $this->getDefaultFilters();
        $config = $this->filterConfigByMode($config, $mode);
        $config = $this->filterConfigByScope($config, $scope);
        $userRole = Auth::check()
            ? Auth::user()->role
            : UserRole::GUEST;
        $config = $this->filterConfigByRole($config, $userRole);
        return $config;
    }

    protected function getDefaultFilters(): array
    {
        // Get dynamic options
        $recommendations = Recommendation::select('id', 'name')->get()->toArray();
        $hedgeRows = HedgeRow::select('id', 'name')->get()->toArray();
        $hedgeShapes = HedgeShape::select('id', 'name')->get()->toArray();
        $infrastructureTypes = InfrastructureType::with('icon')
            ->get()->map(function ($type) {
                return [
                    'id' => $type->id,
                    'name' => $type->name,
                    'icon' => $type->icon?->file_path,
                ];
            })->toArray();
        $parks = Park::with('icon')->select('id', 'name')->orderBy('name')
            ->get()->map(function ($park) {
                return [
                    'id' => $park->id,
                    'name' => $park->name,
                    'icon' => $park->icon?->file_path,
                ];
            })->toArray();

        $tags = Tag::select('id', 'name', 'type')
            ->orderBy('name')->get()->groupBy('type')
            ->map(fn ($items) => $items->values()->toArray());
        return [ 
            'park' => [
                'name' => 'Парки',
                'slug' => 'park',
                'type' => 'group',
                'scope' => 'global',
                'open' => true,
                'checkbox' => false,
                'children' => [
                    [
                        'name' => '',
                        'slug' => 'parks',
                        'type' => 'infrastructureSelect',
                        'size' => 'big',
                        'checked' => true,
                        'options' => $parks,
                    ],
                ],
            ],
            'infrastructure' => [
                'name' => 'Інфраструктура',
                'slug' => 'infrastructure',
                'type' => 'group',
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
                'children' => [
                    [
                        'name' => 'Роботи',
                        'slug' => 'works',
                        'type' => 'group',
                        'role' => 'worker',
                        'children' => [
                            [
                                'name' => 'Виконання',
                                'slug' => 'completion',
                                'type' => 'multiselect',
                                'options' => [['name' => 'Виконані', 'id' => 'completed'], ['name'=>'Невиконані', 'id'=>'uncompleted']],
                            ],
                            [
                                'name' => 'Рекомендації',
                                'slug' => 'recommendations',
                                'type' => 'multiselect',
                                'options' => $recommendations,
                            ],
                            [
                                'name' => 'Дата доручення',
                                'slug' => 'recommendation_date_range',
                                'type' => 'dates',
                            ],
                            [
                                'name' => 'Дата виконання',
                                'slug' => 'execution_date_range',
                                'type' => 'dates',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Загальні фільтри',
                        'slug' => 'general',
                        'type' => 'group',
                        'checkbox' => false,
                        'children' => [
                            [
                                'name' => 'Стан',
                                'slug' => 'green_state',
                                'type' => 'stateSelect',
                                'options' => GreenState::values(),
                            ],
                            [
                                'name' => 'Виділ',
                                'slug' => 'plot',
                                'type' => 'plots',
                                'role' => 'viewer',
                            ],
                            [
                                'name' => 'Вік – від і до',
                                'slug' => 'age_range',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 150,
                            ],
                            [
                                'name' => 'Теги',
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
                                'name' => 'Класифікація дерев',
                                'slug' => 'species',
                                'type' => 'taxonomy',
                            ],
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => $tags['tree'] ?? [],
                            ],
                            [
                                'name' => 'Висота (м)',
                                'slug' => 'height_m',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 50,
                            ],
                            [
                                'name' => 'Діаметр стовбура (см)',
                                'slug' => 'trunk_diameter_cm',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 100,
                            ],
                            [
                                'name' => 'Нахил (°)',
                                'slug' => 'tilt_degree',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 45,
                            ],
                            [
                                'name' => 'Стан крони (%)',
                                'slug' => 'crown_condition_percent',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 100,
                                'role' => 'viewer',
                            ],
                        ],
                    ],
                    [
                        'name' => 'Кущі',
                        'slug' => 'bushes',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Класифікація кущів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
                            ],
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => $tags['bush'] ?? [],
                            ],
                            [
                                'name' => 'Кількість',
                                'slug' => 'quantity',
                                'type' => 'numeric',
                                'min' => 1,
                                'max' => 50,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Живоплоти',
                        'slug' => 'hedges',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Класифікація живоплотів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
                            ],
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => $tags['hedge'] ?? [],
                            ],
                            [
                                'name' => 'Довжина (м)',
                                'slug' => 'length',
                                'type' => 'numeric',
                                'min' => 0,
                                'max' => 100,
                            ],
                            [
                                'name' => 'Тип ряду',
                                'slug' => 'type_row',
                                'type' => 'multiselect',
                                'options' => $hedgeRows,
                            ],
                            [
                                'name' => 'Форма',
                                'slug' => 'type_shape',
                                'type' => 'multiselect',
                                'options' => $hedgeShapes,
                            ],
                        ],
                    ],
                    [
                        'name' => 'Квіти',
                        'slug' => 'flowers',
                        'type' => 'group',
                        'children' => [
                            [
                                'name' => 'Класифікація квітів',
                                'slug' => 'species',
                                'type' => 'taxonomy',
                            ],
                            [
                                'name' => 'Теги',
                                'slug' => 'tags',
                                'type' => 'multiselect',
                                'options' => $tags['flower'] ?? [],
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

    protected function filterConfigByScope(array $config, string $scope): array
    {
        foreach ($config as $key => &$node) {
            if (isset($node['scope']) && $node['scope'] !== $scope) {
                unset($config[$key]);
                continue;
            }

            if (isset($node['children']) && is_array($node['children'])) {
                $node['children'] = $this->filterConfigByScope($node['children'], $scope);
            }
        }

        return array_values($config);
    }

    protected function filterConfigByMode(array $config, string $mode): array
    {
        if($mode === 'works') {
            $config['green']['open'] = true;
            $config['green']['checked'] = true;
            $config['green']['children'][0]['checked'] = true;
            $config['green']['children'][0]['open'] = true;
            $config['green']['children'][0]['children'][0]['options'][1]['checked'] = true;
            return [
                'park' => $config['park'],
                'green' => $config['green'],
                'infrastructure' => $config['infrastructure'],
            ];
        }

        $config[$mode]['open'] = true;
        if ($mode === 'infrastructure') {
            $config['infrastructure']['checked'] = true;
            return [
                'park' => $config['park'],
                'infrastructure' => $config['infrastructure'],
                'green' => $config['green'],
            ];
        } else {
            $config['green']['checked'] = true;
            $config['infrastructure']['checked'] = true;
            return [
                'park' => $config['park'],
                'green' => $config['green'],
                'infrastructure' => $config['infrastructure'],
            ];
        }
    }
}
