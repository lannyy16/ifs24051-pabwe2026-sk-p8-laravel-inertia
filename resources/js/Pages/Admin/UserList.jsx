import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { DownloadIcon, SearchIcon } from '../../Components/Icons';
import Pagination from '../../Components/Pagination';

export default function UserList({ users, filters }) {
    const [search, setSearch] = useState(filters.search);

    return (
        <div>
            <Head title="Kelola Pengguna" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Kelola Pengguna</h1>
                <div className="flex gap-2">
                    <Link href="/admin/posts" className="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100">Semua Artikel</Link>
                    <a href="/admin/posts/export" className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm text-white hover:bg-indigo-700"><DownloadIcon /> Ekspor Semua</a>
                </div>
            </div>
            <form onSubmit={(e) => { e.preventDefault(); router.get('/admin/users', { search }, { preserveState: true }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1"><SearchIcon className="absolute left-3 top-3 text-slate-400" /><input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari nama atau username..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3" /></div>
                <button type="submit" className="rounded-lg bg-slate-800 px-5 text-white">Cari</button>
            </form>
            <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 text-slate-600"><tr><th className="px-4 py-3">Nama</th><th className="px-4 py-3">Username</th><th className="px-4 py-3">Email</th><th className="px-4 py-3">Role</th><th className="px-4 py-3">Artikel</th><th className="px-4 py-3 text-right">Aksi</th></tr></thead>
                    <tbody className="divide-y divide-slate-100">
                        {users.data.map((u) => (
                            <tr key={u.id}>
                                <td className="px-4 py-3 font-medium">{u.name}</td>
                                <td className="px-4 py-3">@{u.username}</td><td className="px-4 py-3">{u.email}</td>
                                <td className="px-4 py-3"><span className={`rounded-full px-2 py-0.5 text-xs ${u.role === 'admin' ? 'bg-violet-100 text-violet-700' : 'bg-slate-100 text-slate-700'}`}>{u.role}</span></td>
                                <td className="px-4 py-3">{u.posts_count} ({u.published_posts_count} terbit)</td>
                                <td className="px-4 py-3">
                                    <div className="flex justify-end gap-2">
                                        <Link href={`/admin/users/${u.id}/posts`} className="rounded-md border border-slate-300 px-3 py-1 text-xs hover:bg-slate-100">Lihat Artikel</Link>
                                        <a href={`/admin/users/${u.id}/posts/export`} className="rounded-md border border-slate-300 px-3 py-1 text-xs hover:bg-slate-100">Ekspor</a>
                                    </div>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination links={users.links} />
        </div>
    );
}
