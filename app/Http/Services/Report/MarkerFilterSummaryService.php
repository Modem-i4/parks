<?php

namespace App\Http\Services\Report;

use App\Http\Services\MarkerFilters\MarkerFilterConfigService;
use App\Models\Family;
use App\Models\Genus;
use App\Models\Plot;
use App\Models\Species;
use App\Models\Subplot;

class MarkerFilterSummaryService
{
    private const GREEN_STATE_LABELS = [
        'good' => 'Хороший',
        'normal' => 'Задовільний',
        'bad' => 'Незадовільний',
        'planned' => 'Лунка',
        'removed' => 'Видалено',
    ];

    private const ROOT_GROUP_LABELS = [
        'green' => 'Зелені насадження',
        'infrastructure' => 'Інфраструктура',
    ];

    private const TYPE_GROUPS = ['trees', 'bushes', 'hedges', 'flowers'];

    public function __construct(
        private readonly MarkerFilterConfigService $configService,
    ) {}

    public function summarize(
        array $filters,
        string $selection = 'filtered',
        string $mode = 'green',
        string $scope = 'local',
    ): array {
        if ($selection === 'picked') {
            return [$this->formatLine([], 'Тип вибірки', 'ручний вибір маркерів')];
        }

        if (empty($filters)) {
            return [];
        }

        $config = $this->configService->get($mode, $scope);
        $summary = $this->summarizeNodes($config, $filters);

        if (empty($filters['infrastructure']) && !empty($filters['green'])) {
            $summary = $this->removeRootGroup($summary, self::ROOT_GROUP_LABELS['green']);
        }

        return $summary ?: $this->fallbackRootSummary($filters);
    }

    private function summarizeNodes(array $nodes, array $filters, array $parents = []): array
    {
        $summary = [];

        foreach ($nodes as $node) {
            $key = $this->filterKey($node);
            if (!array_key_exists($key, $filters)) {
                continue;
            }

            $summary = array_merge(
                $summary,
                $this->summarizeNode($node, $filters[$key], $parents)
            );
        }

        return $summary;
    }

    private function summarizeNode(array $node, mixed $value, array $parents): array
    {
        $type = $node['type'] ?? null;
        $name = trim((string) ($node['name'] ?? ''));

        if ($type === 'group') {
            if (!is_array($value)) {
                return [];
            }

            $children = $this->summarizeNodes(
                $node['children'] ?? [],
                $value,
                $name === '' ? $parents : [...$parents, $name],
            );

            if ($children) {
                return $children;
            }

            return $this->isMeaningfulEmptyGroup($node, $parents)
                ? [$this->formatLine($parents, $this->emptyGroupLabel($parents), $name)]
                : [];
        }

        if ($type === 'plots') {
            return $this->summarizePlots($value, $parents);
        }

        if ($type === 'taxonomy') {
            return $this->summarizeTaxonomy($value, $parents);
        }

        $label = $this->formatLeafValue($node, $value);
        if ($label === null || $label === '') {
            return [];
        }

        return [$this->formatLine($parents, $name, $label)];
    }

    private function filterKey(array $node): string
    {
        return match ($node['type'] ?? null) {
            'plots' => 'plots',
            'taxonomy' => 'taxonomy',
            default => (string) ($node['slug'] ?? ''),
        };
    }

    private function isMeaningfulEmptyGroup(array $node, array $parents): bool
    {
        $slug = $node['slug'] ?? '';

        return in_array($slug, self::TYPE_GROUPS, true)
            || (!empty($parents) && ($node['checkbox'] ?? true) !== false);
    }

    private function emptyGroupLabel(array $parents): string
    {
        return ($parents[0] ?? null) === 'Парки' ? 'Парк' : 'Тип';
    }

    private function formatLeafValue(array $node, mixed $value): ?string
    {
        if ($this->isEmptyValue($value)) {
            return null;
        }

        return match ($node['type'] ?? null) {
            'multiselect', 'infrastructureSelect', 'stateSelect' => $this->formatOptions($node, $value),
            'numeric' => $this->formatRange($node, $value),
            'dates' => $this->formatDateRange($value),
            default => is_scalar($value) ? (string) $value : null,
        };
    }

    private function formatOptions(array $node, mixed $value): ?string
    {
        if (!is_array($value) || empty($value)) {
            return null;
        }

        $options = collect($node['options'] ?? [])
            ->mapWithKeys(function ($option) {
                if (is_array($option)) {
                    return [(string) ($option['id'] ?? $option['name'] ?? '') => (string) ($option['name'] ?? $option['id'] ?? '')];
                }

                return [(string) $option => self::GREEN_STATE_LABELS[$option] ?? (string) $option];
            });

        $labels = collect($value)
            ->map(fn ($item) => $options->get((string) $item, self::GREEN_STATE_LABELS[$item] ?? (string) $item))
            ->filter()
            ->values();

        return $labels->isNotEmpty() ? $labels->implode(', ') : null;
    }

    private function formatRange(array $node, mixed $value): ?string
    {
        if (!is_array($value) || count($value) < 2) {
            return null;
        }

        [$from, $to] = array_values($value);
        if ($from === ($node['min'] ?? null) && $to === ($node['max'] ?? null)) {
            return null;
        }

        return "{$from}–{$to}";
    }

    private function formatDateRange(mixed $value): ?string
    {
        if (!is_array($value) || count($value) < 2) {
            return null;
        }

        [$from, $to] = array_values($value);
        if (!$from && !$to) {
            return null;
        }

        return trim(($from ? "від {$from}" : '') . ' ' . ($to ? "до {$to}" : ''));
    }

    private function summarizePlots(mixed $value, array $parents): array
    {
        if (!is_array($value) || empty($value)) {
            return [];
        }

        $plotIds = array_map('intval', array_keys($value));
        $subplotIds = collect($value)
            ->flatMap(fn ($item) => $item['subplots'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        $plots = Plot::whereIn('id', $plotIds)->pluck('name', 'id');
        $subplots = $subplotIds->isEmpty()
            ? collect()
            : Subplot::whereIn('id', $subplotIds)->pluck('name', 'id');

        $labels = collect($value)
            ->map(function ($item, $plotId) use ($plots, $subplots) {
                $plotName = $plots->get((int) $plotId, (string) $plotId);
                $subNames = collect($item['subplots'] ?? [])
                    ->map(fn ($id) => $subplots->get((int) $id, (string) $id))
                    ->filter()
                    ->values();

                return $subNames->isNotEmpty()
                    ? "{$plotName} (ділянки: {$subNames->implode(', ')})"
                    : $plotName;
            })
            ->values();

        return $labels->isNotEmpty()
            ? [$this->formatLine($parents, 'Виділи', $labels->implode('; '))]
            : [];
    }

    private function summarizeTaxonomy(mixed $value, array $parents): array
    {
        if (!is_array($value) || empty($value)) {
            return [];
        }

        $labels = collect($value)
            ->map(fn ($entry) => $this->taxonomyEntryLabel((array) $entry))
            ->filter()
            ->values();

        return $labels->isNotEmpty()
            ? [$this->formatLine($parents, 'Класифікація', $labels->implode('; '))]
            : [];
    }

    private function taxonomyEntryLabel(array $entry): ?string
    {
        $type = array_key_first($entry);
        $id = $type ? (int) ($entry[$type] ?? 0) : 0;

        if (!$type || !$id) {
            return null;
        }

        return match ($type) {
            'families' => 'Родина: ' . (Family::find($id)?->name_ukr ?? $id),
            'genus' => 'Рід: ' . (Genus::find($id)?->name_ukr ?? $id),
            'species' => 'Вид: ' . (Species::find($id)?->name_ukr ?? $id),
            default => null,
        };
    }

    private function fallbackRootSummary(array $filters): array
    {
        $roots = collect($filters)
            ->filter(fn ($value, $key) => array_key_exists($key, self::ROOT_GROUP_LABELS) && is_array($value) && empty($value))
            ->map(fn ($value, $key) => self::ROOT_GROUP_LABELS[$key])
            ->values();

        if ($roots->count() === 1) {
            return [$this->formatLine([], 'Тип даних', $roots->first())];
        }

        return [];
    }

    private function removeRootGroup(array $summary, string $root): array
    {
        return collect($summary)
            ->map(function ($item) use ($root) {
                if (($item['path'][0] ?? null) === $root) {
                    $item['path'] = array_values(array_slice($item['path'], 1));
                }

                return $item;
            })
            ->all();
    }

    private function formatLine(array $parents, string $name, string $value): array
    {
        $path = collect($parents)
            ->map(fn ($item) => trim((string) $item))
            ->filter()
            ->values()
            ->all();

        return [
            'path' => $path,
            'label' => trim($name),
            'value' => $value,
        ];
    }

    private function isEmptyValue(mixed $value): bool
    {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }
}
