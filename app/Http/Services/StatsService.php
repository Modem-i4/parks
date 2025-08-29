<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsService
{
    private const CACHE_KEY = 'home_stats:v1';
    private const TTL_MINUTES = 60;

    public function get(): array
    {
        return Cache::tags(['home_stats'])->remember(self::CACHE_KEY, now()->addMinutes(self::TTL_MINUTES), function () {
            return $this->build();
        });
    }

    public function flush(): void
    {
        Cache::tags(['home_stats'])->flush();
    }

    private function build(): array
    {
        $parks          = (int) DB::table('parks')->count();
        $infrastructure = (int) DB::table('infrastructure')->count();
        $works_done     = (int) DB::table('works')->whereNotNull('execution_date')->count();

        $green = $this->greenQualityGrouped();
        $genus = $this->topGenusByGreen();

        return [
            'parks'           => $parks,
            'infrastructure'  => $infrastructure,
            'works_done'      => $works_done,
            'genus'           => $genus,
            'green_good'      => $green['good'],
            'green_normal'    => $green['normal'],
            'green_bad'       => $green['bad'],
            'green_total'     => $green['good'] + $green['normal'] + $green['bad'],
        ];
    }

    private function greenQualityGrouped(): array
    {
        $rows = DB::table('green')
            ->select('green_state', DB::raw('COUNT(*) as cnt'))
            ->groupBy('green_state')
            ->get();

        $result = ['good' => 0, 'normal' => 0, 'bad' => 0];

        foreach ($rows as $r) {
            $state = (string) ($r->green_state ?? '');
            if (array_key_exists($state, $result)) {
                $result[$state] = (int) $r->cnt;
            }
        }

        return $result;
    }

    private function topGenusByGreen()
    {
        $baseQuery = DB::table('green')
            ->join('trees', 'trees.id', '=', 'green.id')
            ->join('species', 'species.id', '=', 'green.species_id')
            ->join('genus', 'genus.id', '=', 'species.genus_id')
            ->whereNotNull('green.species_id')
            ->whereNotNull('species.genus_id');


        $rows = (clone $baseQuery)
            ->select('genus.id as genus_id', 'genus.name_ukr as genus', DB::raw('COUNT(*) as count'))
            ->groupBy('genus.id', 'genus.name_ukr')
            ->orderByDesc('count')
            ->limit(6)
            ->get();

        $total = (clone $baseQuery)->count();

        $topSum = $rows->sum('count');

        $result = [];

        foreach ($rows as $r) {
            $result[] = [
                'id'    => (int) $r->genus_id,
                'name'  => (string) $r->genus,
                'count' => (int) $r->count,
            ];
        }

        $others = $total - $topSum;
        if ($others > 0) {
            $result[] = [
                'id'    => 0,
                'name'  => 'Інші',
                'count' => $others,
            ];
        }

        return $result;
    }

}
