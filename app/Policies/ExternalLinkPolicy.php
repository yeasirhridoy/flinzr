<?php

namespace App\Policies;

use App\Models\ExternalLink;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ExternalLinkPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ExternalLink $externalLink): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return ExternalLink::query()->count() === 0;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ExternalLink $externalLink): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ExternalLink $externalLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ExternalLink $externalLink): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ExternalLink $externalLink): bool
    {
        return false;
    }
}
