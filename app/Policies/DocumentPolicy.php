<?php

namespace App\Policies;

use App\Models\Document;
use App\Models\User;
use App\Services\DocumentService;

class DocumentPolicy
{
    /**
     * Determine whether the user can view any documents.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view/download the document.
     */
    public function download(User $user, Document $document): bool
    {
        return DocumentService::canDownload($user, $document);
    }

    /**
     * Determine whether the user can upload into a given category.
     */
    public function upload(User $user, string $category, ?string $targetOwnerId = null): bool
    {
        return DocumentService::canUpload($user, $category, $targetOwnerId);
    }

    /**
     * Determine whether the user can archive the document.
     */
    public function delete(User $user, Document $document): bool
    {
        if ($user->isOc()) {
            return true;
        }

        return $document->owner_user_id === $user->id;
    }

    /**
     * Determine whether the user can review/verify the document.
     */
    public function review(User $user, Document $document): bool
    {
        return $user->isOc() || ($user->isCoc() && in_array($document->category, ['weekly_report', 'authorization']));
    }
}
