<?php

namespace App\Http\Controllers;

use App\Enums\MoaCustodianParty;
use App\Models\Moa;
use App\Models\MoaTransfer;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;

class MoaCustodyController extends Controller
{
    /**
     * Transfer physical or digital custody of an MOA (Rule BR-16)
     * Custody order is unconstrained: can flow between OC, COC, UA, Student, Company in any order.
     */
    public function transfer(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('transferCustody', $moa);

        $validated = $request->validate([
            'to_party' => ['required', new Enum(MoaCustodianParty::class)],
            'to_user_id' => ['nullable', 'exists:users,id'],
            'note' => ['required', 'string', 'min:3', 'max:500'],
        ]);

        $fromParty = $moa->current_custodian_party;
        $fromUserId = $moa->current_custodian_user_id;
        $toParty = $validated['to_party'];
        $toUserId = $validated['to_user_id'] ?? null;

        DB::transaction(function () use ($moa, $fromParty, $fromUserId, $toParty, $toUserId, $validated) {
            // Update MOA record
            $moa->update([
                'current_custodian_party' => $toParty,
                'current_custodian_user_id' => $toUserId,
            ]);

            // Append immutable custody transfer log
            MoaTransfer::create([
                'moa_id' => $moa->id,
                'from_party' => $fromParty,
                'to_party' => $toParty,
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'actor_id' => Auth::id(),
                'note' => $validated['note'],
                'created_at' => now(),
            ]);

            AuditService::log('moa.custody_transferred', $moa, [
                'from_party' => $fromParty instanceof MoaCustodianParty ? $fromParty->value : $fromParty,
                'to_party' => $toParty instanceof MoaCustodianParty ? $toParty->value : $toParty,
                'transferred_by' => Auth::id(),
                'note' => $validated['note'],
            ]);
        });

        $toLabel = $toParty instanceof MoaCustodianParty ? $toParty->label() : $toParty;

        return redirect()->route('moas.show', $moa)->with(
            'status',
            "MOA custody successfully transferred to {$toLabel}."
        );
    }
}
