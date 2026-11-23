<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Membuat (atau memperbarui) akun Kepala Sekolah untuk monitoring.
 *
 * Kepala sekolah dapat melihat dashboard, data akademik, data PPDB,
 * dan mengunduh laporan PDF, tetapi tidak dapat mengubah data.
 *
 * Kredensial dibaca dari environment agar tidak tersimpan di repository:
 *   KEPALA_SEKOLAH_NAME, KEPALA_SEKOLAH_EMAIL, KEPALA_SEKOLAH_PASSWORD
 * Jika password tidak diisi, password acak dibuat dan ditampilkan sekali.
 */
class KepalaSekolahSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = env('KEPALA_SEKOLAH_EMAIL', 'kepsek@example.com');
        $password = env('KEPALA_SEKOLAH_PASSWORD') ?: Str::password(14);

        $user = User::firstOrNew(['email' => $email]);
        $user->forceFill([
            'name' => env('KEPALA_SEKOLAH_NAME', 'Kepala Sekolah'),
            'password' => $password,
            'email_verified_at' => now(),
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->grantMonitoringAccess($user);

        $this->command?->info("Akun kepala sekolah: {$user->email} / {$password}");
        $this->command?->warn('Jalankan "php artisan config:clear" bila config di-cache.');
    }

    private function grantMonitoringAccess(User $user): void
    {
        $ids = config('access.kepala_sekolah_user_ids', []);
        $ids[] = (string) $user->id;
        $ids = array_values(array_unique($ids));

        config(['access.kepala_sekolah_user_ids' => $ids]);

        $path = base_path('.env');

        // Saat testing, jangan mengubah file .env milik developer.
        if (app()->runningUnitTests() || ! is_file($path)) {
            return;
        }

        $contents = file_get_contents($path);
        $line = 'KEPALA_SEKOLAH_USER_IDS='.implode(',', $ids);

        if (preg_match('/^KEPALA_SEKOLAH_USER_IDS=.*$/m', $contents)) {
            $contents = preg_replace('/^KEPALA_SEKOLAH_USER_IDS=.*$/m', $line, $contents);
        } else {
            $contents = rtrim($contents).PHP_EOL.$line.PHP_EOL;
        }

        file_put_contents($path, $contents);
    }
}
