<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $appUrl = rtrim(
            (string) config('app.url'),
            '/'
        );

        if (str_starts_with($appUrl, 'https://')) {
            URL::forceRootUrl($appUrl);
            URL::forceScheme('https');
        }

        Gate::define(
            'admin-only',
            function (User $user): bool {
                return strtolower(
                    trim((string) $user->role)
                ) === 'admin';
            }
        );

        Gate::define(
            'guru-only',
            function (User $user): bool {
                return strtolower(
                    trim((string) $user->role)
                ) === 'guru'
                    && ! is_null($user->teacher_id);
            }
        );
    }
}
