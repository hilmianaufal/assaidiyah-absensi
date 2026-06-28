<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        if ($user->role !== 'guru') {
            return response()->json([
                'message' => 'Aplikasi Android hanya untuk akun guru.',
            ], 403);
        }

        if (! $user->teacher) {
            return response()->json([
                'message' => 'Akun guru belum terhubung dengan data guru.',
            ], 403);
        }

        $token = $user->createToken('android-app')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('teacher'),
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }
}
