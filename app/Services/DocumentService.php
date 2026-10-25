<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\Document;
use App\Models\DocumentBatch;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class DocumentService
{
    /**
     * Magic byte signatures for authorized file formats
     */
    protected const MAGIC_BYTES = [
        'pdf' => ["\x25\x50\x44\x46"], // %PDF-
        'png' => ["\x89\x50\x4E\x47\x0D\x0A\x1A\x0A"],
        'jpg' => ["\xFF\xD8\xFF"],
        'jpeg' => ["\xFF\xD8\xFF"],
        'docx' => ["\x50\x4B\x03\x04"], // PK.. (Zip container)
    ];

    protected const ALLOWED_EXTENSIONS = ['pdf', 'docx', 'png', 'jpg', 'jpeg'];

    protected const ALLOWED_MIMES = [
        'application/pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'image/png',
        'image/jpeg',
    ];

    /**
     * Check if a role is permitted to upload into a specific category
     */
    public static function canUpload(User $user, string $category, ?string $targetOwnerId = null): bool
    {
        $role = $user->role;

        return match ($category) {
            'resume', 'certification' => $role === UserRole::OC || ($role === UserRole::STUDENT && (string) $user->id === (string) $targetOwnerId),
            'verification' => $role === UserRole::OC || $role === UserRole::PC,
            'moa' => in_array($role, [UserRole::OC, UserRole::UA, UserRole::PC, UserRole::STUDENT]),
            'authorization' => in_array($role, [UserRole::OC, UserRole::COC]),
            'weekly_report' => $role === UserRole::OC || ($role === UserRole::STUDENT && (string) $user->id === (string) $targetOwnerId),
            'template' => $role === UserRole::OC,
            'other' => $role === UserRole::OC || ($role === UserRole::STUDENT && (string) $user->id === (string) $targetOwnerId),
            default => false,
        };
    }

    /**
     * Check if a role is permitted to download a specific document
     */
    public static function canDownload(User $user, Document $document): bool
    {
        $role = $user->role;
        $isOwner = (string) $user->id === (string) $document->owner_user_id;

        if ($role === UserRole::OC) {
            return true;
        }

        return match ($document->category) {
            'resume', 'certification' => match ($role) {
                UserRole::STUDENT => $isOwner,
                UserRole::COC => $user->assignedStudents()->where('user_id', $document->owner_user_id)->exists(),
                UserRole::PC => true, // Will be scoped to applicants in Phase 5/6
                default => false,
            },
            'verification' => match ($role) {
                UserRole::COC, UserRole::UA => true,
                UserRole::PC => $isOwner || ($user->partnerAccount && $user->partnerAccount->company_id == $document->documentable_id),
                default => false,
            },
            'moa' => match ($role) {
                UserRole::UA => true,
                UserRole::COC => $user->assignedStudents()->where('user_id', $document->owner_user_id)->exists(),
                UserRole::PC => true,
                UserRole::STUDENT => $isOwner,
                default => false,
            },
            'authorization', 'weekly_report' => match ($role) {
                UserRole::COC => $user->assignedStudents()->where('user_id', $document->owner_user_id)->exists(),
                UserRole::PC => true,
                UserRole::STUDENT => $isOwner,
                default => false,
            },
            'template' => true,
            'other' => $isOwner,
            default => false,
        };
    }

    /**
     * Request upload slots and generate presigned/signed storage URLs
     */
    public function requestUploadSlots(User $user, array $fileRequests): array
    {
        $batchMax = config('connected.upload_batch_max', 20);
        if (count($fileRequests) > $batchMax) {
            throw ValidationException::withMessages([
                'files' => "Cannot request more than {$batchMax} files per upload batch.",
            ]);
        }

        $batch = DocumentBatch::create([
            'user_id' => $user->id,
            'status' => 'pending',
            'file_count' => count($fileRequests),
        ]);

        $slots = [];

        foreach ($fileRequests as $file) {
            $name = $file['name'];
            $size = (int) $file['size'];
            $declaredMime = strtolower($file['mime'] ?? '');
            $category = $file['category'];
            $ownerId = $file['owner_user_id'] ?? $user->id;

            // Validate extension
            $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            if (! in_array($ext, self::ALLOWED_EXTENSIONS)) {
                throw ValidationException::withMessages([
                    'files' => "File '{$name}' has an unauthorized file extension. Allowed: ".implode(', ', self::ALLOWED_EXTENSIONS),
                ]);
            }

            // Validate size
            $maxMb = ($category === 'resume') ? config('connected.max_resume_size_mb', 5) : config('connected.max_file_size_mb', 10);
            if ($size > ($maxMb * 1024 * 1024)) {
                throw ValidationException::withMessages([
                    'files' => "File '{$name}' exceeds the maximum allowed size of {$maxMb} MB.",
                ]);
            }

            // Authorize upload
            if (! self::canUpload($user, $category, (string) $ownerId)) {
                throw ValidationException::withMessages([
                    'files' => "You are not authorized to upload a '{$category}' document for this record.",
                ]);
            }

            $storageKey = 'documents/'.date('Y/m/').Str::uuid().'.'.$ext;

            $document = Document::create([
                'owner_user_id' => $ownerId,
                'documentable_type' => $file['documentable_type'] ?? null,
                'documentable_id' => $file['documentable_id'] ?? null,
                'category' => $category,
                'original_name' => $name,
                'storage_key' => $storageKey,
                'stored_path' => $storageKey,
                'disk' => 'documents',
                'mime_type' => $declaredMime ?: 'application/octet-stream',
                'size_bytes' => $size,
                'validation_status' => 'pending',
                'uploaded_by' => $user->id,
                'upload_batch_id' => $batch->id,
                'version_no' => 1,
                'is_current' => true,
                'review_status' => 'pending_review',
                'school_year' => config('connected.current_school_year', '2026-2027'),
            ]);

            // Generate presigned PUT URL (or mock signed local URL)
            $uploadUrl = $this->generatePresignedPutUrl($storageKey, $declaredMime);

            $slots[] = [
                'document_id' => $document->id,
                'storage_key' => $storageKey,
                'upload_url' => $uploadUrl,
                'original_name' => $name,
                'size' => $size,
            ];
        }

        return [
            'batch_id' => $batch->id,
            'files' => $slots,
        ];
    }

    /**
     * Confirm batch uploads, verifying size and inspecting magic bytes
     */
    public function confirmBatch(DocumentBatch $batch): array
    {
        $disk = Storage::disk('documents');
        $results = [];

        foreach ($batch->documents()->where('validation_status', 'pending')->get() as $doc) {
            $key = $doc->storage_key;

            if (! $disk->exists($key)) {
                $doc->update([
                    'validation_status' => 'rejected',
                    'validation_remark' => 'Uploaded object was not found in storage.',
                ]);
                $results[] = ['document_id' => $doc->id, 'status' => 'rejected', 'error' => 'Not found in storage'];

                continue;
            }

            $actualSize = $disk->size($key);
            $maxMb = ($doc->category === 'resume') ? config('connected.max_resume_size_mb', 5) : config('connected.max_file_size_mb', 10);

            if ($actualSize > ($maxMb * 1024 * 1024)) {
                $doc->update([
                    'validation_status' => 'rejected',
                    'validation_remark' => "Uploaded file size ({$actualSize} bytes) exceeded {$maxMb} MB limit.",
                ]);
                $results[] = ['document_id' => $doc->id, 'status' => 'rejected', 'error' => 'Size exceeded'];

                continue;
            }

            // Inspect magic bytes from the first 16 bytes
            $headBytes = $disk->readStream($key);
            $sample = fread($headBytes, 16);
            if (is_resource($headBytes)) {
                fclose($headBytes);
            }

            $ext = strtolower(pathinfo($doc->original_name, PATHINFO_EXTENSION));
            if (! $this->verifyMagicBytes($sample, $ext)) {
                $doc->update([
                    'validation_status' => 'rejected',
                    'validation_remark' => "Security check failed: File header content does not match .{$ext} specification.",
                ]);
                $results[] = ['document_id' => $doc->id, 'status' => 'rejected', 'error' => 'Magic byte verification failed'];

                continue;
            }

            // Compute SHA256 checksum
            $fullStream = $disk->readStream($key);
            $ctx = hash_init('sha256');
            while (! feof($fullStream)) {
                hash_update($ctx, fread($fullStream, 8192));
            }
            $checksum = hash_final($ctx);
            if (is_resource($fullStream)) {
                fclose($fullStream);
            }

            $doc->update([
                'size_bytes' => $actualSize,
                'checksum_sha256' => $checksum,
                'validation_status' => 'ok',
                'validation_remark' => null,
            ]);

            $results[] = ['document_id' => $doc->id, 'status' => 'ok'];
        }

        $batch->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);

        return $results;
    }

    /**
     * Generate secure presigned GET download URL
     */
    public function getDownloadUrl(Document $document): string
    {
        $disk = Storage::disk('documents');

        // If S3, generate presigned temporary URL valid for 60 seconds
        if (config('filesystems.disks.documents.driver') === 's3') {
            return $disk->temporaryUrl(
                $document->storage_key,
                now()->addSeconds(60),
                [
                    'ResponseContentDisposition' => 'attachment; filename="'.addslashes($document->original_name).'"',
                ]
            );
        }

        // Local signed URL fallback
        return URL::temporarySignedRoute(
            'documents.download.serve',
            now()->addSeconds(60),
            ['document' => $document->id]
        );
    }

    protected function generatePresignedPutUrl(string $storageKey, string $mime): string
    {
        $disk = Storage::disk('documents');

        if (config('filesystems.disks.documents.driver') === 's3') {
            $client = $disk->getClient();
            $command = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.documents.bucket'),
                'Key' => $storageKey,
                'ContentType' => $mime,
            ]);

            return (string) $client->createPresignedRequest($command, '+10 minutes')->getUri();
        }

        // Local development signed route
        return URL::temporarySignedRoute(
            'documents.upload.direct',
            now()->addMinutes(10),
            ['key' => $storageKey]
        );
    }

    protected function verifyMagicBytes(string $sample, string $ext): bool
    {
        if (! isset(self::MAGIC_BYTES[$ext])) {
            return false;
        }

        foreach (self::MAGIC_BYTES[$ext] as $signature) {
            if (str_starts_with($sample, $signature)) {
                return true;
            }
        }

        return false;
    }
}
