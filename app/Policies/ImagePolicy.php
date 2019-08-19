<?php

namespace App\Policies;

use App\Image;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ImagePolicy
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
     * @param Image $images
     * @return mixed
     */
    public function view(User $user, Image $image)
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
     * @param Image $images
     * @return mixed
     */
    public function update(User $user, Image $images)
    {
        //
    }

    /**
     * Determine whether the user can delete the images.
     *
     * @param User $user
     * @param Image $images
     * @return mixed
     */
    public function delete(User $user, Image $images)
    {
        //
    }

    /**
     * Determine whether the user can restore the images.
     *
     * @param User $user
     * @param Image $images
     * @return mixed
     */
    public function restore(User $user, Image $images)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the images.
     *
     * @param User $user
     * @param Image $images
     * @return mixed
     */
    public function forceDelete(User $user, Image $images)
    {
        //
    }
}
