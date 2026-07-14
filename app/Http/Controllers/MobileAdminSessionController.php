<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileAdminSessionController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $cacheKey = 'mobile_admin_session:' . $token;

        // Cache::pull membuat token hanya bisa dipakai satu kali.
        $payload = Cache::pull($cacheKey);

        abort_unless(
            is_array($payload) &&
            isset($payload['user_id'], $payload['path']),
            403,
            'Link login admin sudah kedaluwarsa atau telah digunakan.'
        );

        $user = User::find($payload['user_id']);

        abort_unless(
            $user && $user->role === 'admin',
            403,
            'Akun tidak memiliki akses admin.'
        );

        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        return redirect()->to($payload['path']);
    }
}
