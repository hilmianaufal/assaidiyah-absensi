<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MobileAdminSessionController extends Controller
{
    public function __invoke(Request $request)
    {
        $target = (string) $request->query('target');
        $userId = (int) $request->query('user');

        if (! in_array(
            $target,
            ['check_in', 'check_out', 'enrollment'],
            true
        )) {
            abort(403, 'Target halaman admin tidak valid.');
        }

        $user = User::find($userId);

        if (
            ! $user ||
            strtolower((string) $user->role) !== 'admin'
        ) {
            abort(403, 'Akun tidak memiliki akses admin.');
        }

        /*
         * Membuat sesi login web Laravel untuk WebView.
         */
        Auth::guard('web')->login($user);

        $request->session()->regenerate();

        $path = match ($target) {
            'check_in' =>
                '/face-attendance?mode=check_in&mobile=1',

            'check_out' =>
                '/face-attendance?mode=check_out&mobile=1',

            'enrollment' =>
                '/face-enrollment?mobile=1',
        };

        $destination = rtrim(
            (string) config('app.url'),
            '/'
        ) . $path;

        return redirect()->away($destination);
    }
}
