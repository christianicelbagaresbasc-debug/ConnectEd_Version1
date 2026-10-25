<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = AuditLog::with('actor')->latest('created_at');

        if ($request->filled('action')) {
            $query->where('action', 'like', '%'.$request->input('action').'%');
        }

        if ($request->filled('actor_id')) {
            $query->where('actor_id', $request->input('actor_id'));
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->input('date'));
        }

        $logs = $query->paginate(20)->withQueryString();

        return view('coordinator.audit-logs.index', compact('logs'));
    }
}
