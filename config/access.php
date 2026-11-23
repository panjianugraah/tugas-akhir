<?php

$parseIds = fn (string $value): array => array_values(array_filter(
    array_map('trim', explode(',', $value)),
    fn ($id) => $id !== ''
));

return [
    // Akses penuh: kelola seluruh data.
    'admin_user_ids' => $parseIds((string) env('ADMIN_USER_IDS', '')),

    // Akses monitoring (hanya lihat): dashboard, data, dan laporan.
    'kepala_sekolah_user_ids' => $parseIds((string) env('KEPALA_SEKOLAH_USER_IDS', '')),
];
