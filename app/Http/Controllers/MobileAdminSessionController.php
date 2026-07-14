<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class MobileAdminSessionController extends Controller
{
    public function __invoke(
        Request $request,
        string $token
    ) {
        $cacheKey = 'mobile_admin_session:' . $token;

        /*
         * Cache::pull mengambil sekaligus menghapus token.
         * Artinya link hanya bisa dipakai satu kali.
         */
        $payload = Cache::pull($cacheKey);

        if (
            ! is_array($payload) ||
            ! isset($payload['user_id'], $payload['path'])
        ) {
            abort(
                403,
                'Link admin sudah kedaluwarsa atau sudah digunakan.'
            );
        }

        $user = User::find($payload['user_id']);

        if (! $user || $user->role !== 'admin') {
            abort(
                403,
                'Akun tidak memiliki akses admin.'
            );
        }

        /*
         * Membuat sesi login web Laravel.
         */
        Auth::guard('web')->login(
            $user,
            false
        );

        /*
         * Mengganti session ID untuk keamanan.
         */
        $request->session()->regenerate();

        return redirect()->to(
            $payload['path']
        );
    }
}
