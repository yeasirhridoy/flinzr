<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerifyRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RecoverPasswordRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $data['name'] = $data['username'];
        unset($data['username']);
        $user = User::create($data);
        $user->sendOtp();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->refresh()->load('country')->loadCount('followers', 'followings')),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('country')->loadCount('followers', 'followings')),
        ]);
    }

    public function logout(): JsonResponse
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'message' => 'Logged out',
        ]);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json(new UserResource($request->user()->load('country')->loadCount('followers', 'followings')));
    }

    public function otp(): JsonResponse
    {
        auth()->user()->sendOtp();
        return response()->json([
            'message' => 'OTP sent',
        ]);
    }

    public function saveFcmToken(Request $request): JsonResponse
    {
        $rules = [
            'fcm_token' => 'string|required',
        ];

        $request->validate($rules);

        $user = auth()->user();
        $user->fcm_token = $request->fcm_token;
        $user->save();
        return response()->json([
            'message' => 'FCM token saved',
        ]);
    }

    public function verifyEmail(EmailVerifyRequest $request): JsonResponse
    {
        $otp = $request->otp;
        $user = auth()->user();
        if (!cache()->has('otp_' . $user->email)) {
            return response()->json([
                'message' => 'OTP is invalid or expired',
            ], 401);
        } elseif ($otp != cache()->get('otp_' . $user->email)) {
            return response()->json([
                'message' => 'OTP is invalid',
            ], 401);
        } else {
            cache()->forget('otp_' . $user->email);
            $user->markEmailAsVerified();
            return response()->json([
                'message' => 'Email verified',
            ]);
        }
    }

    public function recoverPassword(RecoverPasswordRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();
        $user->sendOtp();
        return response()->json([
            'message' => 'OTP sent',
        ]);
    }

    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = User::where('email', $data['email'])->first();
        if ($user->validateOtp($data['otp'])) {
            $user->password = bcrypt($data['password']);
            $user->save();
            return response()->json([
                'message' => 'Password reset successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'OTP is invalid',
            ], 401);
        }
    }

    public function updateUser(UpdateUserRequest $request)
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        if (isset($data['username'])) {
            $data['name'] = $data['username'];
            unset($data['username']);
        }
        $user = auth()->user();
        $user->update($data);
        return response()->json(new UserResource($user));
    }
}
