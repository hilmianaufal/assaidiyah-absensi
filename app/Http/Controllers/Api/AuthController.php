<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($data)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        $user = $request->user();
        $user->load('teacher');

        if (! in_array($user->role, ['admin', 'guru'], true)) {
            Auth::logout();

            return response()->json([
                'message' => 'Role akun tidak diizinkan menggunakan aplikasi.',
            ], 403);
        }

        if ($user->role === 'guru' && ! $user->teacher) {
            Auth::logout();

            return response()->json([
                'message' => 'Akun guru belum terhubung dengan data guru.',
            ], 403);
        }

        $token = $user
            ->createToken('android-app')
            ->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('teacher');

        return response()->json([
            'role' => $user->role,
            'user' => $user,
        ]);
    }

    public function adminWebSession(Request $request)
    {
        $user = $request->user();

        if (! $user || $user->role !== 'admin') {
            return response()->json([
                'message' => 'Menu ini hanya dapat dibuka oleh admin.',
            ], 403);
        }

        $data = $request->validate([
            'target' => [
                'required',
                Rule::in([
                    'check_in',
                    'check_out',
                    'enrollment',
                ]),
            ],
        ]);

        $path = match ($data['target']) {
            'check_in' => '/face-attendance?mode=check_in&mobile=1',
            'check_out' => '/face-attendance?mode=check_out&mobile=1',
            'enrollment' => '/face-enrollment?mobile=1',
        };

        $token = Str::random(64);

        Cache::put(
            'mobile_admin_session:' . $token,
            [
                'user_id' => $user->id,
                'path' => $path,
            ],
            now()->addMinutes(2)
        );

        return response()->json([
            'url' => url('/mobile/admin/session/' . $token),
            'expires_in' => 120,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
