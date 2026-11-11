import { Link } from '@inertiajs/react';

/** Tabel artikel read-only untuk monitoring admin. */
export default function PostTable({ posts, showAuthor = true }) {
    return (
        <div className="overflow-x-auto rounded-xl border border-slate-200 bg-white">
            <table className="w-full text-left text-sm">
                <thead className="bg-slate-50 text-slate-600"><tr><th className="px-4 py-3">Judul</th>{showAuthor && <th className="px-4 py-3">Penulis</th>}<th className="px-4 py-3">Kategori</th><th className="px-4 py-3">Status</th><th className="px-4 py-3">Dibuat</th></tr></thead>
                <tbody className="divide-y divide-slate-100">
                    {posts.length === 0 && <tr><td colSpan={5} className="px-4 py-10 text-center text-slate-500">Tidak ada artikel.</td></tr>}
                    {posts.map((p) => (
                        <tr key={p.id}>
                            <td className="px-4 py-3 font-medium">{p.is_published ? <Link href={`/articles/${p.slug}`} className="hover:text-indigo-700">{p.title}</Link> : p.title}</td>
                            {showAuthor && <td className="px-4 py-3">{p.user?.name}</td>}
                            <td className="px-4 py-3">{p.category}</td>
                            <td className="px-4 py-3"><span className={`rounded-full px-2 py-0.5 text-xs ${p.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{p.is_published ? 'Terbit' : 'Draf'}</span></td>
                            <td className="px-4 py-3">{new Date(p.created_at).toLocaleDateString('id-ID')}</td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
