import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Flash from '../Components/Flash';
import GlobalLoading from '../Components/GlobalLoading';
import { CloseIcon, LoginIcon, MenuIcon, UserPlusIcon } from '../Components/Icons';

/** Layout publik: Sticky Top Navbar, tata letak membaca leluasa, footer editorial (tanpa sidebar). */
function PublicShell({ children }) {
    const { auth, appName } = usePage().props;
    const user = auth.user;

    return (
        <div className="flex min-h-screen flex-col">
            <GlobalLoading />
            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/90 backdrop-blur">
                <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
                    <Link href="/" className="flex items-center gap-2 text-xl font-bold text-indigo-700">
                        <img src="/favicon.svg" alt="" className="h-7 w-7" /> {appName}
                    </Link>
                    <nav className="flex items-center gap-2 text-sm">
                        <Link href="/articles" className="hidden rounded-md px-3 py-2 hover:bg-slate-100 sm:block">Artikel</Link>
                        {user ? (
                            <>
                                <Link href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'} className="flex items-center gap-2 rounded-md bg-indigo-600 px-3 py-2 font-medium text-white hover:bg-indigo-700">
                                    {user.profile_photo_url ? (
                                        <img src={user.profile_photo_url} alt="" className="h-5 w-5 rounded-full object-cover" />
                                    ) : (
                                        <span className="flex h-5 w-5 items-center justify-center rounded-full bg-white/30 text-xs">{user.name.charAt(0).toUpperCase()}</span>
                                    )}
                                    {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                </Link>
                                <button type="button" onClick={() => router.post('/logout')} className="rounded-md px-3 py-2 hover:bg-slate-100">Keluar</button>
                            </>
                        ) : (
                            <>
                                <Link href="/login" className="flex items-center gap-1 rounded-md px-3 py-2 font-medium hover:bg-slate-100"><LoginIcon /> Masuk</Link>
                                <Link href="/register" className="flex items-center gap-1 rounded-md bg-indigo-600 px-3 py-2 font-medium text-white hover:bg-indigo-700"><UserPlusIcon /> Daftar</Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <main className="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
                <Flash />
                {children}
            </main>

            <footer className="border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-6xl flex-col items-center justify-between gap-3 px-4 py-6 text-sm text-slate-500 sm:flex-row">
                    <p>© {new Date().getFullYear()} {appName} — tempat ide menemukan pembacanya.</p>
                    <nav className="flex gap-4">
                        <Link href="/" className="hover:text-indigo-600">Beranda Publik</Link>
                        <Link href="/login" className="hover:text-indigo-600">Halaman Login</Link>
                        <Link href="/register" className="hover:text-indigo-600">Halaman Register</Link>
                    </nav>
                </div>
            </footer>
        </div>
    );
}

/** Layout dashboard (Penulis & Admin) dengan sidebar. */
function DashboardShell({ children }) {
    const { auth, appName } = usePage().props;
    const { url: current } = usePage();
    const user = auth.user;
    const [open, setOpen] = useState(false);

    const links = [
        { href: '/dashboard/posts', label: 'Artikel Saya', show: true },
        { href: '/profile', label: 'Profil', show: true },
        { href: '/admin/users', label: 'Kelola Pengguna', show: user?.role === 'admin' },
        { href: '/admin/posts', label: 'Semua Artikel', show: user?.role === 'admin' },
    ].filter((l) => l.show);

    const isActive = (href) => current === href || current.startsWith(href + '/') || current.startsWith(href + '?');

    return (
        <div className="min-h-screen lg:flex">
            <GlobalLoading />
            <div className="flex items-center justify-between bg-indigo-700 px-4 py-3 text-white lg:hidden">
                <span className="font-bold">{appName}</span>
                <button type="button" onClick={() => setOpen((v) => !v)} aria-label="Menu">{open ? <CloseIcon /> : <MenuIcon />}</button>
            </div>

            <aside className={`${open ? 'block' : 'hidden'} w-full shrink-0 bg-indigo-800 text-indigo-100 lg:block lg:min-h-screen lg:w-64`}>
                <div className="hidden px-6 py-5 text-xl font-bold text-white lg:block">{appName}</div>
                <div className="px-6 pb-4 text-sm lg:pt-0">
                    <p className="font-medium text-white">{user?.name}</p>
                    <p className="text-indigo-300">@{user?.username} · {user?.role}</p>
                </div>
                <nav className="flex flex-col gap-1 px-3">
                    {links.map((l) => (
                        <Link key={l.href} href={l.href} onClick={() => setOpen(false)} className={`rounded-md px-3 py-2 text-sm ${isActive(l.href) ? 'bg-white/15 font-semibold text-white' : 'hover:bg-white/10'}`}>{l.label}</Link>
                    ))}
                    <Link href="/" className="rounded-md px-3 py-2 text-sm hover:bg-white/10">Lihat Blog Publik</Link>
                    <button type="button" onClick={() => router.post('/logout')} className="rounded-md px-3 py-2 text-left text-sm hover:bg-white/10">Keluar</button>
                </nav>
            </aside>

            <main className="min-w-0 flex-1 p-4 sm:p-8">
                <Flash />
                {children}
            </main>
        </div>
    );
}

/** Satu layout untuk seluruh halaman: memilih shell berdasarkan nama komponen halaman. */
export default function AppLayout({ children }) {
    const { component } = usePage();
    const isPublic = component.startsWith('Public/') || component.startsWith('Auth/');

    return isPublic ? <PublicShell>{children}</PublicShell> : <DashboardShell>{children}</DashboardShell>;
}
