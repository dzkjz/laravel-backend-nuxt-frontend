<?php

namespace App\Http\Controllers;

use App\Notifications\PasswordResetRequestNotification;
use App\Notifications\PasswordResetSuccessNotification;
use App\PasswordReset;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);

        $user = User::where('email', $request->get('email'))->first();
        if (!$user) {
            return response()->json([
                'message' => 'We cant find a user with that e-mail address.',
            ], 404);
        }
        $passwordReset = PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => Str::random(60),
            ]
        );

        //如果确有重置，发通知。
        if ($user && $passwordReset) {
            $user->notify(
                new PasswordResetRequestNotification($passwordReset->token)
            );
        }

        return response()->json(
            [
                'message' => 'We have e-mailed your password reset link!',
            ]
        );
    }

    // find the token password reset
    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        //token没找到
        if (!$passwordReset) {
            return response()->json(
                [
                    'message' => 'This password reset token is invalid',
                ],
                404
            );
        }

        //判断重置链接是否已经发布超过720分钟了
        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            //超时失效
            $passwordReset->delete();

            return response()->json(
                [
                    'message' => 'This password reset token is invalid.',
                ],
                404
            );
        }

        //返回该PasswordReset模型实例
        return response()->json($passwordReset);
    }

    //重置密码
    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|min:6',
            'token' => 'required|string',
        ]);

        $passwordReset = PasswordReset::where([
            ['token', $request->get('token')],
            ['email', $request->get('email')],
        ])->first();

        if (!$passwordReset) {
            return response()->json([
                'message' => 'This password reset token is invalid.',
            ], 404);
        }

        $user = User::where('email', $passwordReset->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'We cant find a user with that e-mail address.',
            ], 404);
        }
        $user->password = Hash::make($request->get('password'));
        $user->save();

        //数据库中删除reset 实例数据
        $passwordReset->delete();

        //通知用户密码重置成功
        $user->notify(new PasswordResetSuccessNotification($passwordReset));

        return response()->json($user);
    }
}
