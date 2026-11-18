import { Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState } from 'react';
import logo from '../../../storage/app/public/smp.jpg';

const sectionMenus = [
    { label: 'Beranda', target: 'home' },
    { label: 'Tentang', target: 'about' },
    { label: 'Visi & Misi', target: 'vision' },
    { label: 'Program', target: 'programs' },
];

const pageMenus = [
    { label: 'Galeri', href: '/galeri' },
    { label: 'Pengumuman', href: '/pengumuman' },
    { label: 'PPDB', href: '/ppdb' },
    { label: 'Repository', href: '/repository' },
];

const desktopLinkClass =
    'whitespace-nowrap text-sm font-medium text-gray-700 transition hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700';

const mobileLinkClass =
    'block rounded-lg px-3 py-3 text-sm font-medium text-gray-700 transition hover:bg-blue-50 hover:text-blue-700 focus-visible:outline-2 focus-visible:outline-blue-700';

export default function Navbar() {
    const [isOpen, setIsOpen] = useState(false);

    const closeMenu = () => setIsOpen(false);

    return (
        <header className="border-b border-gray-100 bg-white">
            <nav
                aria-label="Navigasi utama"
                className="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8"
            >
                <div className="flex min-h-20 items-center justify-between gap-4">
                    {/* Logo dan nama sekolah */}
                    <Link
                        href="/"
                        onClick={closeMenu}
                        aria-label="Beranda SMP Angkasa Adi Soemarmo"
                        className="flex shrink-0 items-center gap-3 rounded-lg transition hover:opacity-80 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-700"
                    >
                        <img
                            src={logo}
                            alt="Logo SMP Angkasa Adi Soemarmo"
                            className="h-12 w-12 shrink-0 object-contain"
                        />

                        {/* Nama sekolah hanya tampil pada layar >= 1024px */}
                        <span className="hidden text-sm font-semibold leading-tight text-gray-900 lg:block">
                            SMP Angkasa
                            <br />
                            Adi Soemarmo
                        </span>
                    </Link>

                    {/* Menu desktop */}
                    <div className="hidden items-center gap-5 xl:flex">
                        {sectionMenus.map((menu) => (
                            <a
                                key={menu.target}
                                href={`/#${menu.target}`}
                                className={desktopLinkClass}
                            >
                                {menu.label}
                            </a>
                        ))}

                        {pageMenus.map((menu) => (
                            <Link
                                key={menu.href}
                                href={menu.href}
                                className={desktopLinkClass}
                            >
                                {menu.label}
                            </Link>
                        ))}
                    </div>

                    {/* Login dan tombol menu mobile */}
                    <div className="flex shrink-0 items-center gap-2 sm:gap-3">
                        <Link
                            href="/login"
                            onClick={closeMenu}
                            className="inline-flex min-h-11 items-center justify-center rounded-lg bg-blue-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-700 sm:px-5"
                        >
                            Login
                        </Link>

                        <button
                            type="button"
                            onClick={() => setIsOpen((current) => !current)}
                            aria-label={isOpen ? 'Tutup menu' : 'Buka menu'}
                            aria-expanded={isOpen}
                            aria-controls="navbar-mobile"
                            className="inline-flex size-11 items-center justify-center rounded-lg text-gray-700 transition hover:bg-gray-100 focus-visible:outline-2 focus-visible:outline-blue-700 xl:hidden"
                        >
                            {isOpen ? (
                                <X size={24} aria-hidden="true" />
                            ) : (
                                <Menu size={24} aria-hidden="true" />
                            )}
                        </button>
                    </div>
                </div>

                {/* Menu mobile */}
                {isOpen && (
                    <div
                        id="navbar-mobile"
                        className="space-y-1 border-t border-gray-100 py-3 xl:hidden"
                        onKeyDown={(event) => {
                            if (event.key === 'Escape') {
                                closeMenu();
                            }
                        }}
                    >
                        {sectionMenus.map((menu) => (
                            <a
                                key={menu.target}
                                href={`/#${menu.target}`}
                                onClick={closeMenu}
                                className={mobileLinkClass}
                            >
                                {menu.label}
                            </a>
                        ))}

                        {pageMenus.map((menu) => (
                            <Link
                                key={menu.href}
                                href={menu.href}
                                onClick={closeMenu}
                                className={mobileLinkClass}
                            >
                                {menu.label}
                            </Link>
                        ))}
                    </div>
                )}
            </nav>
        </header>
    );
}