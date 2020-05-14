<?php

namespace App\Http\Controllers;

use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{

    public function register(UserRegisterRequest $request)
    {
        $request['password'] = Hash::make($request['password']);

        $user = User::create($request->all());

        if (!$token = auth()->login($user)) {
            return abort(401);
        }
        return UserResource::make($request->user())->additional(
            [
                'meta' => [
                    'token' => $token,
                ]
            ]
        );
    }

    public function login(UserLoginRequest $request)
    {
        if (!$token = auth()->attempt($request->only(['email', 'password']))) {
            return response()->json(['errors' => 'wrong credentials'], 422);
        }

        return UserResource::make(auth()->user())->additional([
            'meta' => [
                'token' => $token,
            ]
        ]);
    }

    public function user(Request $request)
    {
        return UserResource::make($request->user());
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'logout successful!'], 201);
    }

}
