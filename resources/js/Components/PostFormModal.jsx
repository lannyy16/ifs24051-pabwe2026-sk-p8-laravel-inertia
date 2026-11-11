import { router, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import MarkdownEditor from './MarkdownEditor';
import Modal from './Modal';
import Spinner from './Spinner';

/** Modal Tambah (indigo) & Ubah (amber) — Full Screen. */
export default function PostFormModal({ open, post, categories, onClose }) {
    const editing = Boolean(post);
    const form = useForm({
        title: '', category: 'Umum', excerpt: '', content: '', is_published: false, cover: null,
    });

    useEffect(() => {
        if (open) {
            form.setData({
                title: post?.title ?? '',
                category: post?.category ?? 'Umum',
                excerpt: post?.excerpt ?? '',
                content: post?.content ?? '',
                is_published: post?.is_published ?? false,
                cover: null,
            });
            form.clearErrors();
        }
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, post?.id]);

    const submit = (e) => {
        e.preventDefault();
        const options = { forceFormData: true, preserveScroll: true, onSuccess: () => onClose() };
        if (editing) {
            form.transform((d) => ({ ...d, _method: 'put' })).post(`/dashboard/posts/${post.id}`, options);
        } else {
            form.transform((d) => d).post('/dashboard/posts', options);
        }
    };

    const input = 'w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none';
    const err = (k) => form.errors[k] && <p className="mt-1 text-sm text-red-600">{form.errors[k]}</p>;

    return (
        <Modal
            open={open}
            tone={editing ? 'amber' : 'indigo'}
            title={editing ? 'Ubah Artikel' : 'Tambah Artikel'}
            onClose={onClose}
            footer={
                <>
                    <button type="button" onClick={onClose} className="rounded-lg border border-slate-300 bg-white px-5 py-2 hover:bg-slate-100">Batal</button>
                    <button type="submit" form="post-form" disabled={form.processing} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700">
                        {form.processing && <Spinner />} Simpan
                    </button>
                </>
            }
        >
            <form id="post-form" onSubmit={submit} className="mx-auto max-w-5xl space-y-5">
                <div>
                    <label className="mb-1 block text-sm font-medium" htmlFor="post-title">Judul</label>
                    <input id="post-title" className={input} value={form.data.title} onChange={(e) => form.setData('title', e.target.value)} />
                    {err('title')}
                </div>
                <div className="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label className="mb-1 block text-sm font-medium" htmlFor="post-category">Kategori</label>
                        <select id="post-category" className={input} value={form.data.category} onChange={(e) => form.setData('category', e.target.value)}>
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </select>
                        {err('category')}
                    </div>
                    <div>
                        <label className="mb-1 block text-sm font-medium" htmlFor="post-cover">Foto sampul</label>
                        <input id="post-cover" type="file" accept="image/*" className={input} onChange={(e) => form.setData('cover', e.target.files[0] ?? null)} />
                        {editing && post.cover_url && (
                            <div className="mt-2 flex items-center gap-3">
                                <img src={post.cover_url} alt="" className="h-12 w-20 rounded object-cover" />
                                <button type="button" onClick={() => router.delete(`/dashboard/posts/${post.id}/cover`, { preserveScroll: true, onSuccess: onClose })} className="text-sm text-red-600">Hapus sampul</button>
                            </div>
                        )}
                        {err('cover')}
                    </div>
                </div>
                <div>
                    <label className="mb-1 block text-sm font-medium" htmlFor="post-excerpt">Ringkasan</label>
                    <textarea id="post-excerpt" rows={2} className={input} value={form.data.excerpt} onChange={(e) => form.setData('excerpt', e.target.value)} />
                    {err('excerpt')}
                </div>
                <div>
                    <span className="mb-1 block text-sm font-medium">Isi artikel (Markdown)</span>
                    {open && <MarkdownEditor key={post?.id ?? 'new'} value={form.data.content} onChange={(v) => form.setData('content', v)} />}
                    {err('content')}
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={form.data.is_published} onChange={(e) => form.setData('is_published', e.target.checked)} /> Publikasikan sekarang
                </label>
            </form>
        </Modal>
    );
}
