<?php

namespace App\Http\Controllers;

use App\Enums\ModelType;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AuditController extends Controller
{
    public function index() {
        $options = $this->getOptions();
        return Inertia::render('Admin/Audit', [
            'options' => $options
        ]);
    }

    public function list(Request $req)
    {
        $req->validate([
            'user_id'    => 'nullable|integer',
            'action'     => 'nullable|string|in:created,updated,deleted,restored,rollback',
            'model_type' => 'nullable|string',
            'model_id'   => 'nullable|integer',
            'date_from'  => 'nullable|date',
            'date_to'    => 'nullable|date',
            'page'       => 'nullable|integer',
        ]);

        $q = AuditLog::query()
            ->with('user:id,name')
            ->orderByDesc('created_at');

        if ($req->filled('user_id'))    $q->where('user_id', $req->integer('user_id'));
        if ($req->filled('action'))     $q->where('action', $req->string('action'));
        if ($req->filled('model_type')) $q->where('model_type', $req->string('model_type'));
        if ($req->filled('model_id'))   $q->where('model_id', $req->integer('model_id'));

        if ($req->filled('date_from'))  $q->where('created_at', '>=', $req->date('date_from'));
        if ($req->filled('date_to'))    $q->where('created_at', '<=', $req->date('date_to'));

        $logs = $q->paginate(20);

        return response()->json($logs);
    }

    protected function getOptions()
    {
        $since = now()->subDays(90);

        $users = AuditLog::whereNotNull('user_id')
            ->where('created_at', '>=', $since)
            ->with('user:id,name')
            ->select('user_id')
            ->distinct()
            ->get()
            ->map(fn($r) => ['id' => $r->user_id, 'name' => $r->user?->name ?? "ID {$r->user_id}"]);

        $rawTypes = AuditLog::where('created_at', '>=', $since)->select('model_type')->distinct()->pluck('model_type')->values();
        $modelTypes = $rawTypes
            ->map(function ($fullClass) {
                $short = class_basename($fullClass);
                return [
                    'id' => $short,
                    'name' => ModelType::tryFrom($short)?->label() ?? $short,
                ];
            })
            ->values();

        return [
            'users'  => $users,
            'models' => $modelTypes,
        ];
    }
}
