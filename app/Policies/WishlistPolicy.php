<?php

namespace App\Policies;

use App\Models\User;
use App\Models\wishlist;
use Illuminate\Auth\Access\Response;

class WishlistPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        if ($user->is_admin && $user->permissions->contains('permission_type', 'view')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, wishlist $wishlist): bool
    {
        if ($user->is_admin && $user->permissions->contains('permission_type', 'view')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->is_admin && $user->permissions->contains('permission_type', 'create')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, wishlist $wishlist): bool
    {
        if ($user->is_admin && $user->permissions->contains('permission_type', 'edit')) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, wishlist $wishlist): bool
    {
        if ($user->is_admin && $user->permissions->contains('permission_type', 'delete')) {
            return true;
        }

        return false;
    }
}
