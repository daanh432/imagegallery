<?php

namespace App\Http\Controllers\Api;

use App\Albums;
use App\Http\Controllers\Controller;
use App\Http\Resources\AlbumResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlbumsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('can:view,album')->only('show');
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
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 421);
            } else {
                $album = new Albums();
                $album->name = $request->get('name');
                $album->description = $request->get('description');
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
     * @param Albums $albums
     * @return Response
     */
    public function show(User $user, Albums $album)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Albums $albums
     * @return Response
     */
    public function update(Request $request, User $user, Albums $album)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Albums $albums
     * @return Response
     */
    public function destroy(User $user, Albums $album)
    {
        //
    }
}
