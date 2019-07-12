<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Images;
use App\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

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
     * @param Request $request
     * @param User $user
     * @return ImageResource|JsonResponse
     */
    public function store(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'newImage' => ['required', 'image', 'mimetypes:image/jpeg,image/jpg,image/png', 'max:10240']
        ]);
        if ($validator->fails() || !$request->file('newImage')->isValid()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        } else {
            $normalImage = (string)Image::make($request->file('newImage'))->encode('jpg', 80);
            $thumbImage = (string)Image::make($request->file('newImage'))->resize(1000, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->encode('jpg', 70);
            $path = 'images/' . $user->id . '/' . Str::random(10) . '_' . $request->file('newImage')->hashName();
            $thumbPath = 'images/' . $user->id . '/thumb/' . Str::random(10) . '_' . $request->file('newImage')->hashName();
            $image = new Images();
            $image->name = $request->file('newImage')->getClientOriginalName();
            $image->description = null;
            $image->url = Storage::disk('public')->put($path, $normalImage) ? $path : null;
            $image->thumbUrl = Storage::disk('public')->put($thumbPath, $thumbImage) ? $thumbPath : null;
            $image->user_id = $user->id;
            $image->save();
            return new ImageResource($image);
        }
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
        $validator = Validator::make($request->all(), [
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
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param User $user
     * @param Images $image
     * @return Response
     */
    public function destroy(User $user, Images $image)
    {
        $image->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Image successfully deleted'
        ], 200);
    }
}
