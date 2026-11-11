import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { DownloadIcon, SearchIcon } from '../../Components/Icons';
import Pagination from '../../Components/Pagination';
import PostTable from '../../Components/PostTable';

export default function PostList({ posts, filters }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <div>
            <Head title="Semua Artikel" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Monitoring Semua Artikel</h1>
                <a href="/admin/posts/export" className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"><DownloadIcon /> Ekspor Excel</a>
            </div>
            <form onSubmit={(e) => { e.preventDefault(); router.get('/admin/posts', { search }, { preserveState: true }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1"><SearchIcon className="absolute left-3 top-3 text-slate-400" /><input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3" /></div>
                <button type="submit" className="rounded-lg bg-slate-800 px-5 text-white">Cari</button>
            </form>
            <PostTable posts={posts.data} />
            <Pagination links={posts.links} />
        </div>
    );
}
