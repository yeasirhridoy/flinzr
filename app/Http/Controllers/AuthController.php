<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmailVerifyRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RecoverPasswordRequest;
use App\Http\Requests\RegisterDeviceRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\Device;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function registerDevice(RegisterDeviceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['email'] = $data['username'];
        if (!filter_var($data['username'], FILTER_VALIDATE_EMAIL)) {
            $user = User::where('username', $data['username'])->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }
            $data['email'] = $user->email;
        }
        unset($data['username']);
        $deviceDetails = $data['device_details'];
        unset($data['device_details']);
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();

        $userDevice = Device::query()->where('user_id', $user->id);

        if ($userDevice->exists() && $userDevice->first()->device_details != $deviceDetails && $userDevice->first()->device_added_at && $userDevice->first()->device_added_at->diffInDays(now()) < 60) {
            return response()->json([
                'message' => 'already_logged_in',
            ], 401);
        } elseif ($userDevice->exists() && $userDevice->first()->device_details != $deviceDetails && $userDevice->first()->device_added_at && $userDevice->first()->device_added_at->diffInDays(now()) >= 60) {
            $userDevice->update([
                'device_details' => $deviceDetails,
                'device_added_at' => now(),
            ]);
        } elseif (!$userDevice->exists()) {
            $userDevice->create([
                'user_id' => $user->id,
                'device_details' => $deviceDetails,
                'device_added_at' => now(),
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->load('country')->loadCount('followers', 'followings')),
        ]);
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['password'] = bcrypt($data['password']);
        $deviceDetails = $data['device_details'];
        unset($data['device_details']);
        $user = User::create($data);
        $user->sendOtp();
        $token = $user->createToken('auth_token')->plainTextToken;

        Device::create([
            'user_id' => $user->id,
            'device_details' => $deviceDetails,
            'device_added_at' => now(),
        ]);

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user->refresh()->load('country')->loadCount('followers', 'followings')),
        ]);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['email'] = $data['username'];
        if (!filter_var($data['username'], FILTER_VALIDATE_EMAIL)) {
            $user = User::where('username', $data['username'])->first();
            if (!$user) {
                return response()->json([
                    'message' => 'Invalid credentials',
                ], 401);
            }
            $data['email'] = $user->email;
        }
        unset($data['username']);
        $deviceDetails = $data['device_details'];
        unset($data['device_details']);
        if (!auth()->attempt($data)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $user = auth()->user();

        $userDevice = Device::query()->where('user_id', $user->id);

        if ($userDevice->exists() && $userDevice->first()->device_details != $deviceDetails) {
            return response()->json([
                'message' => json_encode([
                    'message' => 'already_logged_in',
                    'remaining' => ceil(60 - $userDevice->first()->device_added_at->diffInDays(now())),
                ]),
            ], 401);
        } elseif (!$userDevice->exists()) {
            $userDevice->create([
                'user_id' => $user->id,
                'device_details' => $deviceDetails,
                'device_added_at' => now(),
            ]);
        }

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
        $user = $request->user();
        $subscription = $user->subscription;
        $cacheKey = 'daily-coin-claim-' . auth('sanctum')->id() . '-' . now()->format('Y-m-d');
        $user->received_coin = false;
        if ($subscription && ($subscription->ends_at >= now() || $subscription->ends_at == null) && !cache()->has($cacheKey)) {
            auth('sanctum')->user()->increment('coin', 1);
            cache()->put($cacheKey, true, now()->addDay());
            $user->received_coin = true;
        }
        return response()->json(new UserResource($user->load('country')->loadCount('followers', 'followings')));
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
            'fcm_token' => 'required',
            'device_type' => 'required',
            'device_details' => 'required',
        ];

        $request->validate($rules);

        $fcmToken = $request->fcm_token;
        $deviceType = $request->device_type;
        $deviceDetails = $request->device_details;

        Device::query()->updateOrCreate([
            'user_id' => auth('sanctum')->id(),
            'device_details' => $deviceDetails,
        ], [
            'is_logged' => true,
            'is_active_notification' => true,
            'fcm_token' => $fcmToken,
            'device_type' => $deviceType,
        ]);

        return response()->json([
            'message' => 'FCM token saved',
        ]);
    }

    private function makeUpUserAgent($userAgent): array
    {
        $userAgent = utf8_decode(urldecode($userAgent));
        $agentDetails = explode(' ', $userAgent);
        $appDetails = array();

        if (count($agentDetails) > 2) {
            $appDetails[] = $agentDetails[0] . " " . $agentDetails[1] . " " . $agentDetails[2];
            if (count($agentDetails) > 3)
                $appDetails[] = $agentDetails[3];
            if (count($agentDetails) > 4)
                $appDetails[] = $agentDetails[4];
            if (count($agentDetails) > 5)
                $appDetails[] = $agentDetails[5];
            if (count($agentDetails) > 6)
                $appDetails[] = $agentDetails[6];
        } else {
            $appDetails[] = $agentDetails[0] . " " . $agentDetails[1];
        }

        $device = array();
        $deviceId = array();
        if (str_contains(strtolower($userAgent), 'ios')) {
            for ($i = 7; $i < count($agentDetails); $i++) {
                if ($i >= 7 && $i < 14 && count($agentDetails) >= 7) {
                    if ($i == 8)
                        $device[] = $agentDetails[$i] . ";";
                    else
                        $device[] = $agentDetails[$i];
                }

                if ($i >= 14 && count($agentDetails) >= 14) {
                    $deviceId[] = $agentDetails[$i];
                }
            }
        } else {
            for ($i = 7; $i < count($agentDetails); $i++) {
                if ($i >= 7 && $i < 12 && count($agentDetails) >= 7) {
                    if ($i == 8)
                        $device[] = $agentDetails[$i] . ";";
                    else if ($i == 10 && $agentDetails[$i] > 20) {
                    } else
                        $device[] = $agentDetails[$i];
                }

                if ($i >= 12 && count($agentDetails) >= 12) {
                    $deviceId[] = $agentDetails[$i];
                }
            }
        }
        $appDetails[] = "(" . implode(" ", $device) . ")";
        $appDetails[] = implode("-", $deviceId);

        return $appDetails;
    }

    public function verifyEmail(EmailVerifyRequest $request): UserResource|JsonResponse
    {
        $otp = $request->otp;
        $user = User::where('email', $request->email)->firstOrFail();
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
            if ($request->is_reg) {
                $user->markEmailAsVerified();

                $referredBy = $user->referred_by;
                if ($referredBy) {
                    $referrer = User::where('referral_code', $referredBy)->first();
                    if ($referrer) {
                        $referrer->coin = $referrer->coin + 10;
                        $referrer->save();
                    }
                }

                return new UserResource($user->load('country')->loadCount('followers', 'followings'));
            } else {
                return response()->json([
                    'message' => true,
                ]);
            }
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
        $user->password = bcrypt($data['password']);
        $user->save();
        return response()->json([
            'message' => 'Password reset successfully',
        ]);
    }

    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }
        if (isset($data['username'])) {
            $data['name'] = $data['username'];
            unset($data['username']);
        }

//        if ($request->image) {
//            $imageData = $request->image;
//
//            $image = base64_decode(preg_replace('/^data:image\/\w+;base64,/', '', $imageData));
//
//            $imageName = Str::random(32);
//
//            $s3Path = 'users/' . $imageName;
//            Storage::put($s3Path, $image,'public');
//
//            $data['image'] = $s3Path;
//        }
//        if ($request->image) {
//            $svgData = $request->image;
//
//            $imageName = Str::random(32) . '.svg';
//
//            $s3Path = 'users/' . $imageName;
//
//            Storage::put($s3Path, $svgData, 'public');
//
//            $data['image'] = $s3Path;
//        }

        $user = auth()->user();
        $user->update($data);
        return response()->json(new UserResource($user));
    }

    /**
     * @throws ValidationException
     */
    public function updatePassword(UpdatePasswordRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = auth()->user();
        if (!auth()->attempt(['email' => $user->email, 'password' => $data['current_password']])) {
            throw ValidationException::withMessages([
                'current_password' => 'Current password is incorrect',
            ]);
        }
        $user->password = bcrypt($data['password']);
        $user->save();
        return response()->json([
            'message' => 'Password updated successfully',
        ]);
    }
}
