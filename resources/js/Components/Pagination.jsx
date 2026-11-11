import { Link } from '@inertiajs/react';

export default function Pagination({ links = [] }) {
    if (links.length <= 3) return null;

    return (
        <nav className="mt-6 flex flex-wrap items-center justify-center gap-1" aria-label="Paginasi">
            {links.map((link, i) => {
                const label = link.label.replace('&laquo;', '«').replace('&raquo;', '»');
                const cls = `rounded-md border px-3 py-1.5 text-sm ${link.active ? 'border-indigo-600 bg-indigo-600 text-white' : 'border-slate-200 bg-white text-slate-700 hover:bg-slate-100'} ${link.url ? '' : 'pointer-events-none opacity-40'}`;
                return link.url ? (
                    <Link key={i} href={link.url} className={cls} preserveScroll>{label}</Link>
                ) : (
                    <span key={i} className={cls}>{label}</span>
                );
            })}
        </nav>
    );
}
