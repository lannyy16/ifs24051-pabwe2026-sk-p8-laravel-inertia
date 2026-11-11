import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { DownloadIcon, SearchIcon } from '../../Components/Icons';
import Pagination from '../../Components/Pagination';
import PostTable from '../../Components/PostTable';

export default function UserPostList({ author, posts, stats, filters }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <div>
            <Head title={`Artikel ${author.name}`} />
            <Link href="/admin/users" className="text-sm text-indigo-600">← Kembali ke pengguna</Link>
            <div className="mb-6 mt-2 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel {author.name} <span className="text-base font-normal text-slate-500">@{author.username} · hanya baca</span></h1>
                <a href={`/admin/users/${author.id}/posts/export`} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"><DownloadIcon /> Ekspor Excel</a>
            </div>
            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <div className="rounded-xl border border-indigo-200 bg-indigo-50 p-5"><p className="text-sm">Total Artikel</p><p className="text-3xl font-bold">{stats.total}</p></div>
                <div className="rounded-xl border border-emerald-200 bg-emerald-50 p-5"><p className="text-sm">Total Selesai</p><p className="text-3xl font-bold">{stats.published}</p></div>
                <div className="rounded-xl border border-amber-200 bg-amber-50 p-5"><p className="text-sm">Belum Selesai</p><p className="text-3xl font-bold">{stats.draft}</p></div>
            </div>
            <form onSubmit={(e) => { e.preventDefault(); router.get(`/admin/users/${author.id}/posts`, { search }, { preserveState: true }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1"><SearchIcon className="absolute left-3 top-3 text-slate-400" /><input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3" /></div>
                <button type="submit" className="rounded-lg bg-slate-800 px-5 text-white">Cari</button>
            </form>
            <PostTable posts={posts.data} showAuthor={false} />
            <Pagination links={posts.links} />
        </div>
    );
}
