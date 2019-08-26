<?php

namespace App\Http\Controllers\Api;

use App\Album;
use App\Http\Controllers\Controller;
use App\Http\Resources\ImageResource;
use App\Image;
use App\User;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image as InterventionImage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImagesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['show']);
        $this->middleware('can:view,image')->only(['update', 'destroy']);
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
            return response()->json(['status' => 'Unauthorized', 'message' => 'You do not have permissions to view this users images.'], 401);
        }
    }

    public function show(Request $request, User $user, String $imagePath)
    {
        $image = Image::where('url', '=', $imagePath)->orWhere('thumbUrl', '=', $imagePath)->get()->first();
        if ($this->Guard()->check() && $this->Guard()->user() != null) {
            abort_if($this->Guard()->user() == null || $this->Guard()->user()->cant('view', $image), 403);
            return $this->ReturnImage($user, $imagePath);
        } else if ($request->get('albumId', null) != null && $image != null) {
            $album = Album::find($request->get('albumId', null));
            if ($image->Albums()->get()->contains($album->id)) {
                if ($album != null && $album->access_level === 2) {
                    return $this->ReturnImage($user, $imagePath);
                } else if ($album != null && $album->access_level === 1 && Hash::check($request->get('token', null), $album->access_password)) {
                    return $this->ReturnImage($user, $imagePath);
                }
            }
        }

        abort(404);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @param User $user
     * @return ImageResource|JsonResponse
     */
    public function store(Request $request, User $user)
    {
        if (Auth::user()->IsAdmin() || Auth::user()->id === $user->id) {
            $validator = Validator::make($request->all(), [
                'newImage' => ['required', 'image', 'mimetypes:image/jpeg,image/jpg,image/png', 'max:10240'],
                'albumId' => ['nullable', 'integer', 'exists:albums,id'],
                'date' => ['required']
            ]);
            if ($validator->fails() || !$request->file('newImage')->isValid()) {
                return response()->json([
                    'status' => 'error',
                    'errors' => $validator->errors()
                ], 422);
            } else {
                $normalImage = (string)InterventionImage::make($request->file('newImage'))->encode('jpg', 80);
                $thumbImage = (string)InterventionImage::make($request->file('newImage'))->resize(1000, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })->encode('jpg', 70);

                // Generates unique file names for the images
                $path = Str::random(10) . '_' . $request->file('newImage')->hashName();
                $thumbPath = '/thumb/' . Str::random(10) . '_' . $request->file('newImage')->hashName();

                $albumId = $request->get('albumId', null);

                $image = new Image();
                $image->name = $request->file('newImage')->getClientOriginalName();
                $image->description = null;
                $image->date = Carbon::createFromTimestamp($request->get('date') / 1000)->toDateTimeString();

                // Stores images in /storage/app/images/XX/
                $image->url = Storage::disk('local')->put($this->ImagePath($user, $path, false), $normalImage) ? $path : null;
                // Stores thumbs in /storage/app/images/XX/thumb/
                $image->thumbUrl = Storage::disk('local')->put($this->ImagePath($user, $thumbPath, false), $thumbImage) ? $thumbPath : null;

                // Stores which user the image belongs to
                $image->user_id = $user->id;
                $image->save();
                $albumId != null ? $image->Albums()->sync($albumId) : null;
                return new ImageResource($image);
            }
        } else {
            return response()->json([
                'status' => 'Unauthorized',
                'message' => 'You are not allowed to upload images for this user'
            ], 401);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param Image $images
     * @return Response
     */
    public function update(Request $request, User $user, Image $image)
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
     * @param Image $image
     * @return Response
     */
    public function destroy(User $user, Image $image)
    {
        Storage::disk('local')->delete($this->ImagePath($user, $image->url, false));
        Storage::disk('local')->delete($this->ImagePath($user, $image->thumbUrl, false));
        $image->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Image successfully deleted'
        ], 200);
    }

    /**
     * @param User $user
     * @param string $imageFileName
     * @param bool $absolute
     * @return string
     */
    private function ImagePath(User $user, string $imageFileName, bool $absolute)
    {
        if ($absolute) {
            return storage_path('app/images/' . $user->id . '/' . $imageFileName);
        }
        return 'images/' . $user->id . '/' . $imageFileName;
    }

    /**
     * @param User $user
     * @param String $imagePath
     * @return BinaryFileResponse
     */
    private function ReturnImage(User $user, String $imagePath)
    {
        $path = $this->ImagePath($user, $imagePath, true);
        if (file_exists($path)) {
            return response()->file($path, ['Content-Type' => 'image/jpeg']);
        } else {
            abort(404, 'File not found');
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
