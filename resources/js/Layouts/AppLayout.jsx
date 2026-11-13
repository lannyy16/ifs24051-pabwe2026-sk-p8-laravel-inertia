import { useEffect, useState } from 'react';
import { Link, router, usePage } from '@inertiajs/react';
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
        const offStart = router.on('start', () => {
            setProcessing(true);
        });

        const offFinish = router.on('finish', () => {
            setProcessing(false);
        });

        return () => {
            offStart();
            offFinish();
        };
    }, []);

    return (
        <div className="flex min-h-screen flex-col">
            {/* Progress Bar */}
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

            {/* Header */}
            <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 backdrop-blur">
                <nav
                    aria-label="Navigasi utama"
                    className="mx-auto flex h-16 max-w-7xl items-center justify-between px-5"
                >
                    {/* Brand */}
                    <Link
                        href="/"
                        aria-label="BlogVerse Pro - Beranda"
                        className="flex items-center gap-2 text-xl font-bold text-blue-700"
                    >
                        <BookOpen aria-hidden="true" />
                        <span>BlogVerse Pro</span>
                    </Link>

                    {/* Menu */}
                    <div className="flex items-center gap-4 text-sm font-semibold">
                        {user ? (
                            <>
                                <Link
                                    href="/dashboard/posts"
                                    className="flex items-center gap-2"
                                >
                                    <LayoutDashboard
                                        size={17}
                                        aria-hidden="true"
                                    />
                                    Dashboard
                                </Link>

                                {user.role === 'admin' && (
                                    <Link
                                        href="/admin/users"
                                        className="flex items-center gap-2"
                                    >
                                        <Shield
                                            size={17}
                                            aria-hidden="true"
                                        />
                                        Admin
                                    </Link>
                                )}

                                <span className="hidden text-slate-500 sm:inline">
                                    {user.name}
                                </span>

                                <button
                                    type="button"
                                    onClick={() => router.post('/logout')}
                                    className="flex items-center gap-1 text-rose-600 transition hover:text-rose-700"
                                >
                                    <LogOut
                                        size={16}
                                        aria-hidden="true"
                                    />
                                    Keluar
                                </button>
                            </>
                        ) : (
                            <>
                                <Link
                                    href="/login"
                                    className="flex items-center gap-1 transition hover:text-blue-600"
                                >
                                    <LogIn
                                        size={16}
                                        aria-hidden="true"
                                    />
                                    Masuk
                                </Link>

                                <Link
                                    href="/register"
                                    className="flex items-center gap-1 rounded-lg bg-blue-600 px-4 py-2 text-white transition hover:bg-blue-700"
                                >
                                    <UserPlus
                                        size={16}
                                        aria-hidden="true"
                                    />
                                    Daftar
                                </Link>
                            </>
                        )}
                    </div>
                </nav>
            </header>

            {/* Main Content */}
            <main
                id="main-content"
                className="flex-1"
                tabIndex="-1"
            >
                {children}
            </main>

            {/* Processing Status */}
            {processing && (
                <div
                    role="status"
                    aria-live="polite"
                    className="fixed bottom-5 right-5 z-50 flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-xl"
                >
                    <span className="h-2 w-2 animate-pulse rounded-full bg-blue-400" />
                    Memproses tindakan...
                </div>
            )}

            {/* Footer */}
            <footer className="mt-16 border-t border-slate-200 bg-white">
                <div className="mx-auto flex max-w-7xl flex-wrap justify-between gap-4 px-5 py-8 text-sm text-slate-500">
                    <p>
                        © {new Date().getFullYear()} BlogVerse Pro · Ruang
                        berbagi pengetahuan.
                    </p>

                    <nav
                        aria-label="Navigasi footer"
                        className="flex gap-5"
                    >
                        <Link
                            href="/"
                            className="transition hover:text-blue-600"
                        >
                            Beranda
                        </Link>

                        <Link
                            href="/login"
                            className="transition hover:text-blue-600"
                        >
                            Login
                        </Link>

                        <Link
                            href="/register"
                            className="transition hover:text-blue-600"
                        >
                            Register
                        </Link>
                    </nav>
                </div>
            </footer>
        </div>
    );
}