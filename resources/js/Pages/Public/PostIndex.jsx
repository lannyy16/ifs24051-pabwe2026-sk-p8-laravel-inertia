import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Pagination from '../../Components/Pagination';
import { LoginIcon, SearchIcon, UserPlusIcon } from '../../Components/Icons';

export const formatDate = (iso) => (iso ? new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '');

export default function PostIndex({ posts, categories, filters }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search);

    const go = (params) => router.get('/articles', { ...filters, ...params }, { preserveState: true, replace: true });

    return (
        <div>
            <Head title="Beranda" />
            <section className="mb-10 rounded-3xl bg-gradient-to-br from-indigo-600 to-violet-600 px-6 py-12 text-white sm:px-12">
                <h1 className="max-w-2xl text-3xl font-extrabold sm:text-5xl">Tempat ide menemukan pembacanya.</h1>
                <p className="mt-4 max-w-xl text-indigo-100">Baca artikel pilihan seputar teknologi, pendidikan, gaya hidup, bisnis, dan opini dari para penulis komunitas kami.</p>
                {!auth.user && (
                    <div className="mt-6 flex flex-wrap gap-3">
                        <Link href="/login" className="flex items-center gap-2 rounded-lg bg-white px-5 py-2.5 font-semibold text-indigo-700 hover:bg-indigo-50"><LoginIcon /> Masuk</Link>
                        <Link href="/register" className="flex items-center gap-2 rounded-lg border border-white/60 px-5 py-2.5 font-semibold hover:bg-white/10"><UserPlusIcon /> Daftar Penulis</Link>
                    </div>
                )}
            </section>

            <form onSubmit={(e) => { e.preventDefault(); go({ search }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1">
                    <SearchIcon className="absolute left-3 top-3 text-slate-400" />
                    <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari judul atau isi artikel..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3" />
                </div>
                <button type="submit" className="rounded-lg bg-indigo-600 px-5 font-medium text-white hover:bg-indigo-700">Cari</button>
            </form>

            <div className="mb-8 flex flex-wrap gap-2">
                <button type="button" onClick={() => go({ category: '' })} className={`rounded-full border px-4 py-1.5 text-sm ${!filters.category ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100'}`}>Semua</button>
                {categories.map((c) => (
                    <button key={c.value} type="button" onClick={() => go({ category: c.value })} className={`rounded-full border px-4 py-1.5 text-sm ${filters.category === c.value ? 'border-indigo-600 bg-indigo-600 text-white' : 'bg-white hover:bg-slate-100'}`}>{c.value}</button>
                ))}
            </div>

            {posts.data.length === 0 ? (
                <p className="rounded-xl border border-dashed border-slate-300 py-16 text-center text-slate-500">Belum ada artikel yang cocok.</p>
            ) : (
                <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {posts.data.map((p) => (
                        <Link key={p.id} href={`/articles/${p.slug}`} className="group overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md">
                            {p.cover_url ? (
                                <img src={p.cover_url} alt={p.title} className="h-44 w-full object-cover" />
                            ) : (
                                <div className="flex h-44 items-center justify-center bg-gradient-to-br from-indigo-100 to-violet-100 text-4xl font-bold text-indigo-300">{p.title.charAt(0)}</div>
                            )}
                            <div className="p-5">
                                <span className="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-medium text-indigo-700">{p.category}</span>
                                <h2 className="mt-3 text-lg font-bold group-hover:text-indigo-700">{p.title}</h2>
                                {p.excerpt && <p className="mt-2 line-clamp-3 text-sm text-slate-600">{p.excerpt}</p>}
                                <p className="mt-4 text-xs text-slate-500">{p.user?.name} · {formatDate(p.published_at)}</p>
                            </div>
                        </Link>
                    ))}
                </div>
            )}
            <Pagination links={posts.links} />
        </div>
    );
}
