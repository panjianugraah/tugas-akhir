<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('manage-users', function (User $user) {
            return in_array(
                (string) $user->id,
                config('access.admin_user_ids', []),
                true
            );
        });
        Gate::define('access-admin', function (User $user) {
            return in_array(
                (string) $user->id,
                config('access.admin_user_ids', []),
                true
            );
        });

        // Kepala sekolah: hanya melihat (monitoring) dashboard, data, dan laporan.
        Gate::define('kepala-sekolah', function (User $user) {
            return in_array(
                (string) $user->id,
                config('access.kepala_sekolah_user_ids', []),
                true
            );
        });

        // Halaman monitoring dapat dibuka admin maupun kepala sekolah.
        Gate::define('view-monitoring', function (User $user) {
            return Gate::forUser($user)->any(['access-admin', 'kepala-sekolah']);
        });
        $this->configureDefaults();
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null,
        );
    }
}
