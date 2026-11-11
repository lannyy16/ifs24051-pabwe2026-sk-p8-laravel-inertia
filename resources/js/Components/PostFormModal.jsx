
import { router, useForm } from '@inertiajs/react';
import { useEffect } from 'react';
import MarkdownEditor from './MarkdownEditor';
import Modal from './Modal';
import Spinner from './Spinner';

/** Modal Tambah dan Ubah Artikel. */
export default function PostFormModal({
    open,
    post,
    categories = [],
    onClose,
}) {
    const editing = Boolean(post);

    const form = useForm({
        title: '',
        category: 'Umum',
        excerpt: '',
        content: '',
        is_published: false,
        cover: null,
    });

    // Isi form ketika modal dibuka.
    useEffect(() => {
        if (!open) return;

        form.setData({
            title: post?.title ?? '',
            category: post?.category ?? 'Umum',
            excerpt: post?.excerpt ?? '',
            content: post?.content ?? '',
            is_published: post?.is_published ?? false,
            cover: null,
        });

        form.clearErrors();

        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [open, post?.id]);

    // Simpan artikel baru atau perubahan artikel.
    const submit = (e) => {
        e.preventDefault();

        if (form.processing) return;

        const options = {
            forceFormData: true,
            preserveScroll: true,

            onSuccess: () => {
                form.reset();
                onClose();
            },
        };

        if (editing) {
            // Gunakan POST dengan method spoofing untuk upload file.
            form.transform((data) => ({
                ...data,
                _method: 'put',
            }));

            form.post(`/dashboard/posts/${post.id}`, options);
        } else {
            // Pastikan tidak ada method spoofing dari proses edit sebelumnya.
            form.transform((data) => ({
                ...data,
            }));

            form.post('/dashboard/posts', options);
        }
    };

    // Hapus foto sampul artikel.
    const deleteCover = () => {
        if (!post?.id || form.processing) return;

        if (!window.confirm('Yakin ingin menghapus foto sampul artikel ini?')) {
            return;
        }

        router.delete(`/dashboard/posts/${post.id}/cover`, {
            preserveScroll: true,
            onSuccess: () => onClose(),
        });
    };

    const input =
        'w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-100';

    const err = (key) =>
        form.errors[key] ? (
            <p className="mt-1 text-sm text-red-600">
                {form.errors[key]}
            </p>
        ) : null;

    return (
        <Modal
            open={open}
            tone={editing ? 'amber' : 'indigo'}
            title={editing ? 'Ubah Artikel' : 'Tambah Artikel'}
            onClose={onClose}
            footer={
                <>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={form.processing}
                        className="rounded-lg border border-slate-300 bg-white px-5 py-2 hover:bg-slate-100 disabled:opacity-50"
                    >
                        Batal
                    </button>

                    <button
                        type="submit"
                        form="post-form"
                        disabled={form.processing}
                        className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        {form.processing && <Spinner />}
                        {form.processing ? 'Menyimpan...' : 'Simpan'}
                    </button>
                </>
            }
        >
            <form
                id="post-form"
                onSubmit={submit}
                encType="multipart/form-data"
                className="mx-auto max-w-5xl space-y-5"
            >
                {/* Judul */}
                <div>
                    <label
                        htmlFor="post-title"
                        className="mb-1 block text-sm font-medium"
                    >
                        Judul
                    </label>

                    <input
                        id="post-title"
                        name="title"
                        type="text"
                        required
                        maxLength={255}
                        className={input}
                        value={form.data.title}
                        onChange={(e) =>
                            form.setData('title', e.target.value)
                        }
                    />

                    {err('title')}
                </div>

                {/* Kategori dan foto sampul */}
                <div className="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label
                            htmlFor="post-category"
                            className="mb-1 block text-sm font-medium"
                        >
                            Kategori
                        </label>

                        <select
                            id="post-category"
                            name="category"
                            required
                            className={input}
                            value={form.data.category}
                            onChange={(e) =>
                                form.setData('category', e.target.value)
                            }
                        >
                            {categories.map((category) => (
                                <option
                                    key={category.value}
                                    value={category.value}
                                >
                                    {category.label}
                                </option>
                            ))}
                        </select>

                        {err('category')}
                    </div>

                    <div>
                        <label
                            htmlFor="post-cover"
                            className="mb-1 block text-sm font-medium"
                        >
                            Foto sampul
                        </label>

                        <input
                            id="post-cover"
                            name="cover"
                            type="file"
                            accept="image/*"
                            className={input}
                            onChange={(e) =>
                                form.setData(
                                    'cover',
                                    e.target.files?.[0] ?? null
                                )
                            }
                        />

                        {err('cover')}

                        {editing && post?.cover_url && (
                            <div className="mt-2 flex items-center gap-3">
                                <img
                                    src={post.cover_url}
                                    alt="Sampul artikel"
                                    className="h-12 w-20 rounded object-cover"
                                />

                                <button
                                    type="button"
                                    onClick={deleteCover}
                                    className="text-sm text-red-600 hover:text-red-800"
                                >
                                    Hapus sampul
                                </button>
                            </div>
                        )}
                    </div>
                </div>

                {/* Ringkasan */}
                <div>
                    <label
                        htmlFor="post-excerpt"
                        className="mb-1 block text-sm font-medium"
                    >
                        Ringkasan
                    </label>

                    <textarea
                        id="post-excerpt"
                        name="excerpt"
                        rows={2}
                        required
                        className={input}
                        value={form.data.excerpt}
                        onChange={(e) =>
                            form.setData('excerpt', e.target.value)
                        }
                    />

                    {err('excerpt')}
                </div>

                {/* Isi artikel */}
                <div>
                    <label className="mb-1 block text-sm font-medium">
                        Isi artikel (Markdown)
                    </label>

                    {open && (
                        <MarkdownEditor
                            key={post?.id ?? 'new'}
                            value={form.data.content}
                            onChange={(value) =>
                                form.setData('content', value)
                            }
                        />
                    )}

                    {err('content')}
                </div>

                {/* Status publikasi */}
                <div>
                    <label
                        htmlFor="post-published"
                        className="flex items-center gap-2 text-sm"
                    >
                        <input
                            id="post-published"
                            name="is_published"
                            type="checkbox"
                            checked={Boolean(form.data.is_published)}
                            onChange={(e) =>
                                form.setData(
                                    'is_published',
                                    e.target.checked
                                )
                            }
                        />

                        Publikasikan sekarang
                    </label>

                    {err('is_published')}
                </div>
            </form>
        </Modal>
    );
}
