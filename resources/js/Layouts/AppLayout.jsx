
import { useEffect, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import {
    BookOpen,
    LayoutDashboard,
    Shield,
    LogOut,
    LogIn,
    UserPlus,
} from 'lucide-react';

export default function AppLayout({ children, publicPage = false }) {
    const [processing, setProcessing] = useState(false);
    const { auth } = usePage().props;
    const user = auth?.user;

    useEffect(() => {
        const offStart = router.on('start', () => setProcessing(true));
        const offFinish = router.on('finish', () => setProcessing(false));

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    return (
        <div className="min-h-screen flex flex-col">
            <div
                aria-hidden="true"
                className={`fixed inset-x-0 top-0 z-[60] h-1 transition-opacity duration-200 ${
                    processing ? 'opacity-100' : 'opacity-0'
                }`}
            >
                <div
                    className={`h-full bg-blue-600 transition-all duration-500 ${
                        processing ? 'w-3/4' : 'w-0'
                    }`}
                />
            </div>

            <header className="sticky top-0 z-30 bg-white/95 backdrop-blur border-b border-slate-200">
                <nav
                    aria-label="Navigasi utama"
                    className="max-w-7xl mx-auto px-5 h-16 flex items-center justify-between"
                >
                    <Link
                        href="/"
                        aria-label="BlogVerse Pro - Beranda"
                        className="flex items-center gap-2 font-bold text-xl text-blue-700"
                    >
                        <BookOpen aria-hidden="true" />
                        <span>BlogVerse Pro</span>
                    </Link>

                    <div className="flex items-center gap-4 text-sm font-semibold">
                        {user ? (
                            <>
                                <Link
                                    href="/dashboard/posts"
                                    className="flex gap-2 items-center"
                                >
                                    <LayoutDashboard size={17} aria-hidden="true" />
                                    Dashboard
                                </Link>

                                {user.role === 'admin' && (
                                    <Link
                                        href="/admin/users"
                                        className="flex gap-2 items-center"
                                    >
                                        <Shield size={17} aria-hidden="true" />
                                        Admin
                                    </Link>
                                )}

                                <span className="hidden sm:inline text-slate-500">
                                    {user.name}
                                </span>

                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className="flex gap-1 items-center text-rose-600"
                                >
                                    <LogOut size={16} aria-hidden="true" />
                                    Keluar
                                </button>
                            </>
                        ) : (
                            <>
                                <Link
                                    href="/login"
                                    className="flex gap-1 items-center"
                                >
                                    <LogIn size={16} aria-hidden="true" />
                                    Masuk
                                </Link>

                                <Link
                                    href="/register"
                                    className="rounded-lg bg-blue-600 text-white px-4 py-2 flex gap-1 items-center"
                                >
                                    <UserPlus size={16} aria-hidden="true" />
                                    Daftar
                                </Link>
                            </>
                        )}
                    </div>
                </nav>
            </header>

            <main id="main-content" className="flex-1">
                {children}
            </main>

            {processing && (
                <div
                    role="status"
                    aria-live="polite"
                    className="fixed bottom-5 right-5 z-50 rounded-full bg-slate-900 text-white px-4 py-2 shadow-xl text-sm flex items-center gap-2"
                >
                    <span className="h-2 w-2 rounded-full bg-blue-400 animate-pulse" />
                    Memproses tindakan...
                </div>
            )}

            <footer className="mt-16 border-t bg-white">
                <div className="max-w-7xl mx-auto px-5 py-8 flex flex-wrap justify-between gap-4 text-sm text-slate-500">
                    <span>
                        © {new Date().getFullYear()} BlogVerse Pro · Ruang berbagi pengetahuan.
                    </span>

                    <nav
                        aria-label="Navigasi footer"
                        className="flex gap-5"
                    >
                        <Link href="/">Beranda</Link>
                        <Link href="/login">Login</Link>
                        <Link href="/register">Register</Link>
                    </nav>
                </div>
            </footer>
        </div>
    );
}
