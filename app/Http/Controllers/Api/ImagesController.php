<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Images;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ImagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
        $this->middleware('can:view,image')->only(['show', 'edit', 'update', 'destroy']);
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
//            return ImageResource::collection($user->Images()->paginate(20)); // Pages support
            return ImageResource::collection($user->Images()->get()); // Lazy load scrolling support
        } else {
            return response()->json(['status' => 'Unauthorized', 'message' => 'You do not have permissions to view this users images.'], 403);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return Response
     */
    public function store(Request $request, User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Images $images
     * @return Response
     */
    public function update(Request $request, User $user, Images $image)
    {
        $validator = Validator::make(
            $request->all(), [
            'name' => 'nullable',
            'description' => 'nullable'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $image->name = $request->get('name', '');
        $image->description = $request->get('description', '');
        $image->save();
        return response()->json([
            'status' => 'success',
            'data' => $image,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Images $images
     * @return Response
     */
    public function destroy(Images $images)
    {
        //
    }
}
