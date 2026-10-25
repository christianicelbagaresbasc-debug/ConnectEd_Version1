<?php

namespace App\Http\Controllers\Attorney;

use App\Enums\MoaNoteType;
use App\Enums\MoaRiskLevel;
use App\Enums\MoaStatus;
use App\Http\Controllers\Controller;
use App\Models\Moa;
use App\Models\MoaNote;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class MoaLegalReviewController extends Controller
{
    /**
     * University Attorney Legal Review Queue (F-010)
     */
    public function index(Request $request): View
    {
        $this->authorize('viewLegalQueue', Moa::class);

        $query = Moa::with([
            'company',
            'studentProfile.user',
            'currentCustodianUser',
            'latestVersion.document',
            'notes.author',
        ]);

        if ($request->input('queue') === 'all') {
            // All MOAs for legal audit
        } else {
            // Default: Under Legal Review or with unresolved notes
            $query->where(function ($q) {
                $q->where('status', MoaStatus::UNDER_LEGAL_REVIEW)
                    ->orWhereHas('unresolvedNotes');
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('moa_number', 'like', "%{$search}%")
                    ->orWhere('summary', 'like', "%{$search}%")
                    ->orWhereHas('company', fn ($cq) => $cq->where('name', 'like', "%{$search}%"));
            });
        }

        $moas = $query->latest()->paginate(10)->withQueryString();

        return view('attorney.index', compact('moas'));
    }

    /**
     * Store internal legal remark or revision note (Rule BR-18)
     */
    public function storeNote(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('addInternalNote', $moa);

        $validated = $request->validate([
            'type' => ['required', new Enum(MoaNoteType::class)],
            'risk_level' => ['nullable', new Enum(MoaRiskLevel::class)],
            'body' => ['required', 'string', 'min:5', 'max:2000'],
        ]);

        $note = MoaNote::create([
            'moa_id' => $moa->id,
            'author_id' => Auth::id(),
            'type' => $validated['type'],
            'risk_level' => $validated['risk_level'] ?? null,
            'body' => $validated['body'],
        ]);

        AuditService::log('moa_note.created', $moa, [
            'note_id' => $note->id,
            'type' => $note->type->value,
            'risk_level' => $note->risk_level?->value,
            'author_id' => Auth::id(),
        ]);

        return redirect()->route('moas.show', $moa)->with(
            'status',
            'Internal legal review remark logged successfully.'
        );
    }

    /**
     * Mark an internal revision note as resolved
     */
    public function resolveNote(Request $request, Moa $moa, MoaNote $note): RedirectResponse
    {
        $this->authorize('resolveNote', $moa);

        if ($note->moa_id !== $moa->id) {
            abort(404);
        }

        $note->update([
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        AuditService::log('moa_note.resolved', $moa, [
            'note_id' => $note->id,
            'resolved_by' => Auth::id(),
        ]);

        return redirect()->route('moas.show', $moa)->with('status', 'Legal revision note marked as resolved.');
    }

    /**
     * University Attorney formally clears the MOA for execution
     */
    public function clearReview(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('update', $moa);

        $validated = $request->validate([
            'target_status' => ['required', new Enum(MoaStatus::class)],
            'clearance_remark' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        // Create formal clearance note
        MoaNote::create([
            'moa_id' => $moa->id,
            'author_id' => Auth::id(),
            'type' => MoaNoteType::LEGAL_REMARK,
            'risk_level' => MoaRiskLevel::LOW,
            'body' => 'LEGAL CLEARANCE: '.$validated['clearance_remark'],
            'resolved_at' => now(),
            'resolved_by' => Auth::id(),
        ]);

        $fromStatus = $moa->status->value;
        $moa->update(['status' => $validated['target_status']]);

        AuditService::log('moa.legal_cleared', $moa, [
            'from_status' => $fromStatus,
            'to_status' => $validated['target_status'] instanceof MoaStatus ? $validated['target_status']->value : $validated['target_status'],
            'attorney_id' => Auth::id(),
            'clearance_remark' => $validated['clearance_remark'],
        ]);

        return redirect()->route('moas.show', $moa)->with(
            'status',
            'MOA has been cleared by University Legal Counsel and transitioned to '.($validated['target_status'] instanceof MoaStatus ? $validated['target_status']->label() : $validated['target_status']).'.'
        );
    }
}
