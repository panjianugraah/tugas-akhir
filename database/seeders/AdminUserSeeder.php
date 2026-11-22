<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@example.com');
        $password = env('ADMIN_PASSWORD', 'password');

        $admin = User::firstOrNew(['email' => $email]);
        $admin->forceFill([
            'name' => env('ADMIN_NAME', 'Admin'),
            'password' => $password,
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->grantAdminAccess($admin);

        $this->command?->info("Admin login: {$admin->email} / {$password}");
    }

    private function grantAdminAccess(User $user): void
    {
        $ids = config('access.admin_user_ids', []);
        $ids[] = (string) $user->id;
        $ids = array_values(array_unique($ids));

        config(['access.admin_user_ids' => $ids]);

        $path = base_path('.env');

        if (! is_file($path)) {
            return;
        }

        $contents = file_get_contents($path);
        $line = 'ADMIN_USER_IDS='.implode(',', $ids);

        if (preg_match('/^ADMIN_USER_IDS=.*$/m', $contents)) {
            $contents = preg_replace('/^ADMIN_USER_IDS=.*$/m', $line, $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);
    }
}
