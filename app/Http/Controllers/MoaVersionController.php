<?php

namespace App\Http\Controllers;

use App\Models\Moa;
use App\Models\MoaVersion;
use App\Services\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MoaVersionController extends Controller
{
    public function store(Request $request, Moa $moa): RedirectResponse
    {
        $this->authorize('update', $moa);

        $validated = $request->validate([
            'document_id' => ['required', 'exists:documents,id'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $nextVersionNo = ($moa->versions()->max('version_no') ?? 0) + 1;

        $version = MoaVersion::create([
            'moa_id' => $moa->id,
            'version_no' => $nextVersionNo,
            'document_id' => $validated['document_id'],
            'uploaded_by' => Auth::id(),
            'note' => $validated['note'] ?? "Version {$nextVersionNo} uploaded.",
        ]);

        AuditService::log('moa.version_uploaded', $moa, [
            'version_no' => $nextVersionNo,
            'document_id' => $version->document_id,
            'uploader' => Auth::id(),
        ]);

        return redirect()->route('moas.show', $moa)->with(
            'status',
            "New MOA Version {$nextVersionNo} attached successfully."
        );
    }
}
