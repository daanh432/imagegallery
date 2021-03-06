<?php

namespace App\Policies;

use App\Album;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AlbumPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any albums.
     *
     * @param User $user
     * @return mixed
     */
    public function viewAny(User $user)
    {
        //
    }

    /**
     * Determine whether the user can view the albums.
     *
     * @param User $user
     * @param Album $albums
     * @return mixed
     */
    public function view(User $user, Album $album)
    {
        return $user->IsAdmin() || $user->id === $album->user_id;
    }

    /**
     * Determine whether the user can create albums.
     *
     * @param User $user
     * @return mixed
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Determine whether the user can update the albums.
     *
     * @param User $user
     * @param Album $albums
     * @return mixed
     */
    public function update(User $user, Album $album)
    {
        return $user->IsAdmin() || $user->id === $album->user_id;
    }

    /**
     * Determine whether the user can delete the albums.
     *
     * @param User $user
     * @param Album $albums
     * @return mixed
     */
    public function delete(User $user, Album $albums)
    {
        //
    }

    /**
     * Determine whether the user can restore the albums.
     *
     * @param User $user
     * @param Album $albums
     * @return mixed
     */
    public function restore(User $user, Album $albums)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the albums.
     *
     * @param User $user
     * @param Album $albums
     * @return mixed
     */
    public function forceDelete(User $user, Album $albums)
    {
        //
    }
}
