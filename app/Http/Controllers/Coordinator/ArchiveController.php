<?php

namespace App\Http\Controllers\Coordinator;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ArchiveController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $tab = $request->input('tab', 'users');

        if ($tab === 'documents') {
            $archivedItems = Document::onlyTrashed()
                ->with(['owner', 'uploader'])
                ->latest('deleted_at')
                ->paginate(15)
                ->withQueryString();
        } else {
            $tab = 'users';
            $archivedItems = User::onlyTrashed()
                ->latest('deleted_at')
                ->paginate(15)
                ->withQueryString();
        }

        return view('coordinator.archive.index', compact('archivedItems', 'tab'));
    }

    public function restore(Request $request, string $type, int $id): RedirectResponse
    {
        $this->authorize('viewAny', User::class);

        $model = match ($type) {
            'users' => User::onlyTrashed()->findOrFail($id),
            'documents' => Document::onlyTrashed()->findOrFail($id),
            default => abort(404, 'Invalid archive type.'),
        };

        $model->restore();

        AuditService::log('record.restored', $model, [
            'type' => $type,
            'restored_by' => Auth::id(),
        ]);

        return back()->with('status', "Archived record #{$id} ({$type}) has been restored.");
    }
}
