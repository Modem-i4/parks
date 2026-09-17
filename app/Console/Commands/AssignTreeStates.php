<?php

namespace App\Console\Commands;

use App\Enums\GreenState;
use App\Models\Marker;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class AssignTreeStates extends Command
{
    protected $signature = 'trees:assign-states
                            {--ignore-tags= : Comma-separated tag names to exclude from the count}
                            {--dry-run : Show totals without saving changes}';

    protected $description = 'Assign tree states by tag count: 0 = good, 1 = normal, 2 or more = bad';

    public function handle(): int
    {
        $ignoredNames = array_values(array_filter(
            array_map('trim', explode(',', (string) $this->option('ignore-tags'))),
            fn (string $name) => $name !== '',
        ));
        $dryRun = (bool) $this->option('dry-run');
        $totals = ['good' => 0, 'normal' => 0, 'bad' => 0];
        $changed = 0;

        Marker::query()
            ->whereHas('green.tree')
            ->whereHas('green', fn (Builder $query) => $query->whereNotIn('green_state', [
                GreenState::PLANNED->value,
                GreenState::REMOVED->value,
            ]))
            ->with('green')
            ->withCount(['tags' => function (Builder $query) use ($ignoredNames) {
                $query->whereNotIn('tags.name', $ignoredNames);
            }])
            ->chunkById(500, function ($markers) use ($dryRun, &$totals, &$changed) {
                foreach ($markers as $marker) {
                    $state = match ((int) $marker->tags_count) {
                        0 => GreenState::GOOD,
                        1 => GreenState::NORMAL,
                        default => GreenState::BAD,
                    };
                    $totals[$state->value]++;

                    $green = $marker->green;
                    if ($green->green_state === $state->value) {
                        continue;
                    }

                    if (! $dryRun) {
                        $green->green_state = $state->value;
                        $green->save();
                    }
                    $changed++;
                }
            });

        $this->table(['good', 'normal', 'bad'], [array_values($totals)]);
        $this->info($dryRun ? "Would update {$changed} trees." : "Updated {$changed} trees.");

        return self::SUCCESS;
    }
}
