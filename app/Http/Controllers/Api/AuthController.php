<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => [
                'required',
                'email',
            ],
            'password' => [
                'required',
                'string',
            ],
        ]);

        $user = User::with('teacher')
            ->where('email', $data['email'])
            ->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        if (! in_array($user->role, ['admin', 'guru'], true)) {
            return response()->json([
                'message' => 'Role akun tidak diizinkan menggunakan aplikasi.',
            ], 403);
        }

        if ($user->role === 'guru' && ! $user->teacher) {
            return response()->json([
                'message' => 'Akun guru belum terhubung dengan data guru.',
            ], 403);
        }

        /*
         * Hapus token Android lama milik user supaya token
         * tidak terus bertambah setiap kali login.
         */
        $user->tokens()
            ->where('name', 'android-app')
            ->delete();

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

        $sessionToken = Str::random(64);

        Cache::put(
            'mobile_admin_session:' . $sessionToken,
            [
                'user_id' => $user->id,
                'path' => $path,
            ],
            now()->addMinutes(5)
        );

        $baseUrl = rtrim(
            config('app.url'),
            '/'
        );

        return response()->json([
            'url' => $baseUrl
                . '/mobile/admin/session/'
                . $sessionToken,

            'expires_in' => 300,
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
