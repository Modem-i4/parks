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

        return [
            'parks'           => $parks,
            'infrastructure'  => $infrastructure,
            'works_done'      => $works_done,
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
}
