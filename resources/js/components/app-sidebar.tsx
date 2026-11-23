import { Link } from '@inertiajs/react';
import { BookOpen, CalendarDays, ClipboardList, FileText, FolderArchive, GraduationCap, Image, LayoutGrid, Megaphone, MegaphoneIcon, Paperclip, School, User, UserRound } from 'lucide-react';
import AppLogo from '@/components/app-logo';
import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { register } from '@/routes';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import type { NavItem } from '@/types';
import { usePage } from '@inertiajs/react';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'PPDB',
        href: '#',
        icon: ClipboardList,
        items: [
            { title: 'Pendaftar', href: '/admin/ppdbadmin', icon: FileText },
            { title: 'Periode PPDB', href: '/admin/periode-ppdb', icon: CalendarDays },
        ],
    },
    {
        title: 'Akademik',
        href: '#',
        icon: GraduationCap,
        items: [
            { title: 'Siswa', href: '/admin/siswa', icon: User },
            { title: 'Guru', href: '/admin/guru', icon: UserRound },
            { title: 'Kelas', href: '/admin/kelas', icon: School },
            { title: 'Mata Pelajaran', href: '/admin/mata-pelajaran', icon: BookOpen },
        ],
    },
    {
        title: 'Pengelolaan Konten',
        href: '#',
        icon: FolderArchive,
        items: [
            { title: 'Pengumuman', href: '/admin/konten/pengumuman', icon: Megaphone },
            { title: 'Galeri', href: '/admin/konten/galeri', icon: Image },
            { title: 'Repository', href: '/admin/konten/repository', icon: FolderArchive },
        ],
    },
    {
        title: 'Laporan',
        href: '/admin/laporan',
        icon: FileText,
    },
];

const footerNavItems: NavItem[] = [

];

export function AppSidebar() {

    const { can } = usePage<{
        can: {
            access_admin: boolean;
            manage_users: boolean;
            view_monitoring: boolean;
            kepala_sekolah: boolean;
        };
    }>().props;

    // Kepala sekolah: menu monitoring saja (tanpa Pengelolaan Konten).
    const visibleNavItems = can.access_admin
        ? mainNavItems
        : can.view_monitoring
            ? mainNavItems.filter((item) => item.title !== 'Pengelolaan Konten')
            : mainNavItems.filter(
                (item) => item.href === '/admin/konten/repository'
            );

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild className='h-14 group-data-[collapsible=icon]:size-12! group-data-[collapsible=icon]:p-1!' >
                            <Link
                                href={
                                    can.view_monitoring
                                        ? '/admin/dashboard'
                                        : '/admin/konten/repository'
                                }
                            >
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={visibleNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                {can.manage_users && (
                    <Link
                        href="/admin/pengguna/tambah"
                        className="rounded-lg border px-4 py-2 text-sm hover:bg-muted"
                    >
                        Tambah Akun
                    </Link>
                )}
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
