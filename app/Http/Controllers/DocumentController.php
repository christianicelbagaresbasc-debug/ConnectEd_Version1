<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentBatch;
use App\Services\AuditService;
use App\Services\DocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * Request upload slots and receive presigned PUT URLs
     */
    public function requestSlots(Request $request): JsonResponse
    {
        $request->validate([
            'files' => ['required', 'array', 'min:1'],
            'files.*.name' => ['required', 'string'],
            'files.*.size' => ['required', 'integer', 'min:1'],
            'files.*.category' => ['required', 'string'],
            'files.*.mime' => ['nullable', 'string'],
            'files.*.documentable_type' => ['nullable', 'string'],
            'files.*.documentable_id' => ['nullable', 'integer'],
            'files.*.owner_user_id' => ['nullable', 'integer'],
        ]);

        $result = $this->documentService->requestUploadSlots(Auth::user(), $request->input('files'));

        return response()->json($result);
    }

    /**
     * Confirm batch completion and trigger integrity & magic-byte verification
     */
    public function confirmBatch(Request $request, DocumentBatch $batch): JsonResponse
    {
        if ($batch->user_id !== Auth::id() && !Auth::user()->isOc()) {
            abort(403, 'Unauthorized batch confirmation.');
        }

        $results = $this->documentService->confirmBatch($batch);

        return response()->json([
            'batch_id' => $batch->id,
            'status' => 'confirmed',
            'files' => $results,
        ]);
    }

    /**
     * Authorized document download with security audit logging
     */
    public function download(Document $document): RedirectResponse|BinaryFileResponse
    {
        $this->authorize('download', $document);

        // Audit sensitive document access by non-owners
        if ($document->owner_user_id !== Auth::id()) {
            AuditService::log('document.downloaded', $document, [
                'category' => $document->category,
                'original_name' => $document->original_name,
                'owner_user_id' => $document->owner_user_id,
            ]);
        }

        $url = $this->documentService->getDownloadUrl($document);

        // If presigned cloud URL or external route, redirect
        if (str_starts_with($url, 'http://') || str_starts_with($url, 'https://')) {
            return redirect()->away($url);
        }

        return redirect($url);
    }

    /**
     * Local direct upload endpoint (mocks S3 presigned PUT in local/test environment)
     */
    public function directUpload(Request $request): JsonResponse
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired upload signature.');
        }

        $key = $request->query('key');
        if (empty($key)) {
            abort(400, 'Missing storage key parameter.');
        }

        // Support both raw binary payload and multipart file uploads
        $content = $request->getContent();
        if (empty($content) && $request->hasFile('file')) {
            $content = file_get_contents($request->file('file')->getRealPath());
        }

        Storage::disk('documents')->put($key, $content);

        return response()->json(['status' => 'uploaded', 'key' => $key]);
    }

    /**
     * Local direct download endpoint (serves signed local files)
     */
    public function directDownloadServe(Request $request, Document $document): BinaryFileResponse
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired download signature.');
        }

        $disk = Storage::disk('documents');
        $path = $disk->path($document->storage_key);

        return response()->download(
            $path,
            $document->original_name,
            [
                'X-Content-Type-Options' => 'nosniff',
                'Content-Type' => $document->mime_type,
            ]
        );
    }
}
