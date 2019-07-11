<?php

namespace App\Policies;

use App\Images;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImagesPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any images.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the images.
     *
     * @param User $user
     * @param Images $images
     * @return mixed
     */
    public function view(User $user, Images $image)
    {
        return $user->IsAdmin() || $user->id === $image->user_id;
    }

    /**
     * Determine whether the user can create images.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the images.
     *
     * @param User $user
     * @param Images $images
     * @return mixed
     */
    public function update(User $user, Images $images)
    {
        //
    }

    /**
     * Determine whether the user can delete the images.
     *
     * @param User $user
     * @param Images $images
     * @return mixed
     */
    public function delete(User $user, Images $images)
    {
        //
    }

    /**
     * Determine whether the user can restore the images.
     *
     * @param User $user
     * @param Images $images
     * @return mixed
     */
    public function restore(User $user, Images $images)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the images.
     *
     * @param User $user
     * @param Images $images
     * @return mixed
     */
    public function forceDelete(User $user, Images $images)
    {
        //
    }
}
