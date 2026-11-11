import { Head, Link, router } from '@inertiajs/react';
import { useRef } from 'react';
import MarkdownViewer from '../../Components/MarkdownViewer';

export default function Show({ post }) {
    const fileInput = useRef(null);

    const upload = (e) => {
        const cover = e.target.files[0];
        if (cover) router.post(`/dashboard/posts/${post.id}/cover`, { cover }, { forceFormData: true, preserveScroll: true });
        e.target.value = '';
    };

    return (
        <div className="mx-auto max-w-3xl">
            <Head title={post.title} />
            <Link href="/dashboard/posts" className="text-sm text-indigo-600">← Kembali ke daftar</Link>
            <div className="mt-4 flex flex-wrap items-center gap-2">
                <span className="rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">{post.category}</span>
                <span className={`rounded-full px-3 py-1 text-xs font-medium ${post.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{post.is_published ? 'Terbit' : 'Draf'}</span>
            </div>
            <h1 className="mt-3 text-3xl font-extrabold">{post.title}</h1>

            <div className="mt-4 flex flex-wrap gap-2 text-sm">
                <button type="button" onClick={() => router.patch(`/dashboard/posts/${post.id}/publish`, {}, { preserveScroll: true })} className="rounded-lg border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">{post.is_published ? 'Jadikan Draf' : 'Publikasikan'}</button>
                <button type="button" onClick={() => fileInput.current.click()} className="rounded-lg border border-slate-300 bg-white px-4 py-2 hover:bg-slate-100">{post.cover_url ? 'Ganti Sampul' : 'Unggah Sampul'}</button>
                <input ref={fileInput} type="file" accept="image/*" className="hidden" onChange={upload} />
                {post.cover_url && <button type="button" onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true })} className="rounded-lg border border-red-200 px-4 py-2 text-red-600 hover:bg-red-50">Hapus Sampul</button>}
                {post.is_published && <Link href={`/articles/${post.slug}`} className="rounded-lg bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700">Lihat di Blog</Link>}
            </div>

            {post.cover_url && <img src={post.cover_url} alt={post.title} className="mt-6 w-full rounded-2xl object-cover" />}
            {post.excerpt && <p className="mt-6 border-l-4 border-indigo-400 pl-4 italic text-slate-600">{post.excerpt}</p>}
            <div className="mt-6 rounded-xl border border-slate-200 bg-white p-6"><MarkdownViewer content={post.content} /></div>
        </div>
    );
}
