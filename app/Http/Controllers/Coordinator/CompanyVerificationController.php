<?php

namespace App\Http\Controllers\Coordinator;

use App\Enums\CompanyVerificationStatus;
use App\Enums\VerificationItemStatus;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyVerificationItem;
use App\Models\CompanyVerificationLog;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Enum;
use Illuminate\View\View;

class CompanyVerificationController extends Controller
{
    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load([
            'verificationItems.requirement',
            'verificationItems.document',
            'verificationItems.reviewer',
            'verificationLogs.actor',
        ]);

        return view('coordinator.companies.verification', compact('company'));
    }

    /**
     * Coordinator reviews a specific checklist requirement item
     */
    public function updateItem(Request $request, Company $company, CompanyVerificationItem $item): RedirectResponse
    {
        $this->authorize('verify', $company);

        if ($item->company_id !== $company->id) {
            abort(404);
        }

        $validated = $request->validate([
            'status' => ['required', new Enum(VerificationItemStatus::class)],
            'remark' => ['nullable', 'string', 'max:1000'],
            'document_id' => ['nullable', 'exists:documents,id'],
        ]);

        $item->update([
            'status' => $validated['status'],
            'remark' => $validated['remark'] ?? null,
            'document_id' => $validated['document_id'] ?? $item->document_id,
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
        ]);

        AuditService::log('company_verification_item.reviewed', $company, [
            'item_id' => $item->id,
            'requirement' => $item->requirement->label,
            'status' => $item->status->value,
            'remark' => $item->remark,
        ]);

        return back()->with('status', "Checklist item '{$item->requirement->label}' updated to {$item->status->label()}.");
    }

    /**
     * Coordinator updates the overall company verification status
     * (pending -> under_review -> verified / rejected; verified -> revoked)
     */
    public function updateStatus(Request $request, Company $company): RedirectResponse
    {
        $this->authorize('verify', $company);

        $validated = $request->validate([
            'verification_status' => ['required', new Enum(CompanyVerificationStatus::class)],
            'remark' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $fromStatus = $company->verification_status->value;
        $toStatus = $validated['verification_status'];

        DB::transaction(function () use ($company, $fromStatus, $toStatus, $validated) {
            $company->update([
                'verification_status' => $toStatus,
            ]);

            CompanyVerificationLog::create([
                'company_id' => $company->id,
                'from_status' => $fromStatus,
                'to_status' => $toStatus instanceof CompanyVerificationStatus ? $toStatus->value : $toStatus,
                'remark' => $validated['remark'],
                'actor_id' => Auth::id(),
                'created_at' => now(),
            ]);

            AuditService::log('company.verification_status_changed', $company, [
                'from_status' => $fromStatus,
                'to_status' => $toStatus instanceof CompanyVerificationStatus ? $toStatus->value : $toStatus,
                'remark' => $validated['remark'],
            ]);
        });

        return back()->with(
            'status',
            "Verification status for '{$company->name}' changed to ".($toStatus instanceof CompanyVerificationStatus ? $toStatus->label() : $toStatus).'.'
        );
    }
}
