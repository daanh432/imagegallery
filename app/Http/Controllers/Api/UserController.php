<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('IsAdmin')->except(['show', 'update']);
    }

    public function index()
    {
        return UserResource::collection(User::paginate(10));
    }

    public function show(User $user)
    {
        if ($this->Guard()->user() != null && $this->Guard()->user()->IsAdmin() || $this->Guard()->user()->id === $user->id) {
            return new UserResource($user);
        } else {
            return response()->json(['status' => 'error', 'message' => 'You\'re not authorized to view this user\'s information.'], 401);
        }
    }

    public function update(Request $request, User $user)
    {

        if (!$this->Guard()->user()->IsAdmin() && !Hash::check($request->get('currentPassword', null), $user->password)) {
            abort(403);
        }
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:200',
            'email' => 'required|email|max:200',
            'role' => 'sometimes|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 421);
        }

        if ($this->Guard()->user()->IsSuperAdmin()) {
            $user->role = $request->get('role', 0);
        }

        $user->name = $request->get('name');
        $user->email = $request->get('email');
        $user->save();

        return new UserResource($user);
    }

    private function Guard()
    {
        return Auth::guard('api');
    }
}
