<?php

namespace App\Models\Concerns;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait LogsChanges
{
    protected array $auditIgnore = [
        'created_at','updated_at','deleted_at','remember_token','password'
    ];

    public static function bootLogsChanges(): void
    {
        static::created(function ($model) {
            $model->writeAudit('created', null, $model->getAuditSnapshot());
        });

        static::updated(function ($model) {
            [$before, $after] = $model->computeAuditDiff();
            if (!empty($after)) {
                $model->writeAudit('updated', $before, $after);
            }
        });

        static::deleted(function ($model) {
            $model->writeAudit('deleted', $model->getAuditSnapshot(), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->writeAudit('restored', null, $model->getAuditSnapshot());
            });
        }
    }

    protected function getAuditSnapshot(): array
    {
        $arr = $this->attributesToArray();
        foreach ($this->auditIgnore ?? [] as $key) unset($arr[$key]);
        return $arr;
    }

    protected function computeAuditDiff(): array
    {
        $before = [];
        $after  = [];
        $original = $this->getOriginal();
        $current  = $this->getAttributes();

        foreach ($current as $key => $val) {
            if (in_array($key, $this->auditIgnore ?? [], true)) continue;
            $orig = $original[$key] ?? null;
            if ($val !== $orig) {
                $before[$key] = $orig;
                $after[$key]  = $val;
            }
        }
        return [$before, $after];
    }

    protected function writeAudit(string $action, ?array $before, ?array $after): void
    {
        if ($action === 'updated' && empty($after)) return;

        AuditLog::create([
            'model_type' => static::class,
            'model_id'   => $this->getKey(),
            'user_id'    => Auth::id(),
            'action'     => $action,
            'before'     => $before,
            'after'      => $after,
        ]);
    }
}
