<?php

namespace App\Http\Controllers\Api;

use App\Album;
use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlbumsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['show']);
        $this->middleware('can:update,album')->only(['edit', 'update', 'destroy']);
    }

    /**
     * Display a listing of the resource.
     *
     * @param User $user
     * @return AnonymousResourceCollection
     */
    public function index(User $user)
    {
        if (Auth::user() && (Auth::user()->IsAdmin() || Auth::user()->id === $user->id)) {
            return AlbumResource::collection($user->Albums()->paginate(20)); // Pages support
//            return AlbumResource::collection($user->Albums()->get()); // Lazy load scrolling support
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @param User $user
     * @return AlbumResource|Response
     */
    public function store(Request $request, User $user)
    {
        if (Auth::user()->IsAdmin() || Auth::user()->id === $user->id) {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:250'],
                'description' => ['required', 'max: 2048'],
                'access_level' => ['required'],
                'access_password' => ['nullable'],
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 421);
            } else {
                $album = new Album();
                $album->name = $request->get('name');
                $album->description = $request->get('description');
                $album->access_level = $request->get('access_level');
                $album->access_password = $request->get('access_password', null);
                $album->user_id = $user->id;
                $album->save();
                return new AlbumResource($album);
            }
        } else {
            return response()->json(['status' => 'Unauthorized', 'message' => 'You are not authorized to create albums for this user'], 401);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Album $albums
     * @return AlbumResource|Response
     */
    public function show(User $user, Album $album)
    {
        if (($this->Guard()->check() && $this->Guard()->user() != null && $this->Guard()->user()->can('view', $album)) || $album->access_level === 2) {
            return response()->json(['data' => new AlbumResource($album), 'authorized' => $this->Guard()->user() != null ? $this->Guard()->user()->can('update', $album) : false]);
        } else {
            return response()->json(['status' => 'Unauthorized', 'Authenticate' => false], 403);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Album $albums
     * @return AlbumResource|Response
     */
    public function update(Request $request, User $user, Album $album)
    {
        if (Auth::user()->IsAdmin() || Auth::user()->id === $user->id) {
            $validator = Validator::make($request->all(), [
                'name' => ['required', 'max:250'],
                'description' => ['required', 'max: 2048'],
                'images' => ['nullable'],
                'access_level' => ['required'],
                'access_password' => ['nullable'],
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 421);
            } else {
                $album->name = $request->get('name');
                $album->description = $request->get('description');
                $album->access_level = $request->get('access_level');
                $album->access_password = $request->get('access_password', $album->access_password);
                $request->get('images', null) != null ? $album->Images()->sync($request->get('images', null)) : null;
                $album->save();
                return new AlbumResource($album);
            }
        } else {
            return response()->json(['status' => 'Unauthorized', 'message' => 'You are not authorized to update albums for this user'], 401);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Album $albums
     * @return Response
     */
    public function destroy(User $user, Album $album)
    {
        if (Auth::user()->IsAdmin() || Auth::user()->id === $user->id) {
            $album->delete();
            return response()->json(['status' => 'success', 'message' => 'Album successfully deleted'], 200);
        } else {
            return response()->json(['status' => 'Unauthorized', 'message' => 'You are not authorized to update albums for this user'], 401);
        }
    }

    /** Returns the authentication guard to use for VueJS frontend
     * @return Guard|StatefulGuard|mixed
     */
    private function Guard()
    {
        return Auth::guard('api');
    }
}
