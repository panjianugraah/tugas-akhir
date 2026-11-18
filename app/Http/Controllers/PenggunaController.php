<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

class PenggunaController extends Controller
{
    public function create()
    {
        return Inertia::render('admin/tambah-pengguna');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => [
                'required',
                'confirmed',
                Password::defaults(),
            ],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Mendukung alur verifikasi email jika diaktifkan.
        event(new Registered($user));

        // Tidak melakukan Auth::login($user).
        // Session admin tetap menggunakan akun admin.
        return back()->with(
            'success',
            'Akun pengguna berhasil ditambahkan.'
        );
    }
}