import { Head, useForm } from '@inertiajs/react';
import { type FormEvent } from 'react';

const inputClass =
    'w-full rounded-lg border bg-background px-3 py-2 text-sm';

export default function TambahPengguna() {
    const {
        data,
        setData,
        post,
        processing,
        errors,
        reset,
        recentlySuccessful,
    } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        post('/admin/pengguna', {
            preserveScroll: true,
            onSuccess: () => reset(),
            onError: () => reset(
                'password',
                'password_confirmation',
            ),
        });
    };

    return (
        <>
            <Head title="Tambah Akun" />

            <main className="p-4 md:p-6">
                <div className="max-w-xl rounded-xl border p-5">
                    <h1 className="text-xl font-bold">
                        Tambah Akun Pengguna
                    </h1>

                    <p className="mt-2 text-sm text-muted-foreground">
                        Buat akun baru tanpa keluar dari akun admin.
                    </p>

                    {recentlySuccessful && (
                        <p
                            role="status"
                            className="mt-4 rounded-lg bg-green-50 p-3 text-sm text-green-800"
                        >
                            Akun berhasil ditambahkan.
                        </p>
                    )}

                    <form
                        onSubmit={submit}
                        className="mt-5 space-y-4"
                    >
                        <div>
                            <label
                                htmlFor="name"
                                className="mb-1 block text-sm"
                            >
                                Nama
                            </label>
                            <input
                                id="name"
                                value={data.name}
                                onChange={(event) =>
                                    setData('name', event.target.value)
                                }
                                required
                                autoComplete="off"
                                className={inputClass}
                            />
                            {errors.name && (
                                <p className="mt-1 text-xs text-red-600">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="email"
                                className="mb-1 block text-sm"
                            >
                                Email
                            </label>
                            <input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(event) =>
                                    setData('email', event.target.value)
                                }
                                required
                                autoComplete="off"
                                className={inputClass}
                            />
                            {errors.email && (
                                <p className="mt-1 text-xs text-red-600">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password"
                                className="mb-1 block text-sm"
                            >
                                Password
                            </label>
                            <input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(event) =>
                                    setData('password', event.target.value)
                                }
                                required
                                autoComplete="new-password"
                                className={inputClass}
                            />
                            {errors.password && (
                                <p className="mt-1 text-xs text-red-600">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <div>
                            <label
                                htmlFor="password_confirmation"
                                className="mb-1 block text-sm"
                            >
                                Konfirmasi Password
                            </label>
                            <input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(event) =>
                                    setData(
                                        'password_confirmation',
                                        event.target.value,
                                    )
                                }
                                required
                                autoComplete="new-password"
                                className={inputClass}
                            />
                        </div>

                        <button
                            disabled={processing}
                            className="rounded-lg bg-blue-700 px-4 py-2 text-sm text-white disabled:opacity-50"
                        >
                            {processing ? 'Menyimpan...' : 'Buat Akun'}
                        </button>
                    </form>
                </div>
            </main>
        </>
    );
}