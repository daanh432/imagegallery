<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('IsAdmin')->except(['show', 'edit', 'update']);
    }

    public function index()
    {
        return UserResource::collection(User::paginate(10));
    }

    public function show(User $user)
    {
        if (Auth::user()->IsAdmin() || Auth::user()->id === $user->id) {
            return new UserResource($user);
        } else {
            return response()->json(['status' => 'error', 'message' => 'You\'re not authorized to view this user\'s information.'], 401);
        }
    }
}
