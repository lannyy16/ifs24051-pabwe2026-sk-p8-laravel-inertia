import { Head, Link, router } from '@inertiajs/react';
import { useRef, useState } from 'react';
import KebabMenu from '../../Components/KebabMenu';
import { DownloadIcon, PlusIcon, SearchIcon, SparkIcon, UploadIcon } from '../../Components/Icons';
import Modal from '../../Components/Modal';
import Pagination from '../../Components/Pagination';
import Spinner from '../../Components/Spinner';
import AiGeneratorModal from '../../Components/AiGeneratorModal';
import PostFormModal from '../../Components/PostFormModal';

const StatCard = ({ label, value, tone }) => (
    <div className={`rounded-xl border p-5 ${tone}`}>
        <p className="text-sm">{label}</p>
        <p className="mt-1 text-3xl font-bold">{value}</p>
    </div>
);

export default function Index({ posts, stats, categories, filters }) {
    const [search, setSearch] = useState(filters.search);
    const [formOpen, setFormOpen] = useState(false);
    const [editing, setEditing] = useState(null);
    const [aiOpen, setAiOpen] = useState(false);
    const [deleting, setDeleting] = useState(null);
    const [deleteBusy, setDeleteBusy] = useState(false);
    const fileInput = useRef(null);

    const openCreate = () => { setEditing(null); setFormOpen(true); };
    const openEdit = (post) => { setEditing(post); setFormOpen(true); };

    const confirmDelete = () => {
        router.delete(`/dashboard/posts/${deleting.id}`, {
            preserveScroll: true,
            onStart: () => setDeleteBusy(true),
            onFinish: () => { setDeleteBusy(false); setDeleting(null); },
        });
    };

    const importFile = (e) => {
        const file = e.target.files[0];
        if (!file) return;
        router.post('/dashboard/posts/import', { file }, { forceFormData: true, preserveScroll: true });
        e.target.value = '';
    };

    return (
        <div>
            <Head title="Artikel Saya" />
            <div className="mb-6 flex flex-wrap items-center justify-between gap-3">
                <h1 className="text-2xl font-bold">Artikel Saya</h1>
                <div className="flex flex-wrap gap-2">
                    <a href="/dashboard/posts/export" className="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100"><DownloadIcon /> Ekspor Excel</a>
                    <button type="button" onClick={() => fileInput.current.click()} className="flex items-center gap-2 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-100"><UploadIcon /> Impor Excel</button>
                    <input ref={fileInput} type="file" accept=".xlsx,.xls" className="hidden" onChange={importFile} />
                    <button type="button" onClick={() => setAiOpen(true)} className="flex items-center gap-2 rounded-lg bg-violet-600 px-4 py-2 text-sm font-medium text-white hover:bg-violet-700"><SparkIcon /> Generate AI</button>
                    <button type="button" onClick={openCreate} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700"><PlusIcon /> Tambah Artikel</button>
                </div>
            </div>

            <div className="mb-6 grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={stats.total} tone="border-indigo-200 bg-indigo-50 text-indigo-900" />
                <StatCard label="Total Selesai" value={stats.published} tone="border-emerald-200 bg-emerald-50 text-emerald-900" />
                <StatCard label="Belum Selesai" value={stats.draft} tone="border-amber-200 bg-amber-50 text-amber-900" />
            </div>

            <form onSubmit={(e) => { e.preventDefault(); router.get('/dashboard/posts', { search }, { preserveState: true }); }} className="mb-4 flex gap-2">
                <div className="relative flex-1">
                    <SearchIcon className="absolute left-3 top-3 text-slate-400" />
                    <input type="search" value={search} onChange={(e) => setSearch(e.target.value)} placeholder="Cari artikel saya..." className="w-full rounded-lg border border-slate-300 py-2.5 pl-10 pr-3" />
                </div>
                <button type="submit" className="rounded-lg bg-slate-800 px-5 text-white">Cari</button>
            </form>

            <ul className="divide-y divide-slate-200 overflow-hidden rounded-xl border border-slate-200 bg-white">
                {posts.data.length === 0 && <li className="p-10 text-center text-slate-500">Belum ada artikel.</li>}
                {posts.data.map((p) => (
                    <li key={p.id} className="flex items-center gap-3 px-4 py-3">
                        <div className="min-w-0 flex-1">
                            <Link href={`/dashboard/posts/${p.id}`} className="block truncate font-medium hover:text-indigo-700">{p.title}</Link>
                            <p className="mt-0.5 text-xs text-slate-500">{p.category} · {new Date(p.created_at).toLocaleDateString('id-ID')}</p>
                        </div>
                        <span className={`rounded-full px-2.5 py-0.5 text-xs font-medium ${p.is_published ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'}`}>{p.is_published ? 'Terbit' : 'Draf'}</span>
                        <button type="button" onClick={() => router.patch(`/dashboard/posts/${p.id}/publish`, {}, { preserveScroll: true })} className="hidden rounded-md border border-slate-300 px-3 py-1 text-xs hover:bg-slate-100 sm:block">
                            {p.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                        </button>
                        <KebabMenu detailHref={`/dashboard/posts/${p.id}`} onEdit={() => openEdit(p)} onDelete={() => setDeleting(p)} />
                    </li>
                ))}
            </ul>
            <Pagination links={posts.links} />

            <PostFormModal open={formOpen} post={editing} categories={categories} onClose={() => setFormOpen(false)} />
            <AiGeneratorModal open={aiOpen} categories={categories} onClose={() => setAiOpen(false)} />
            <Modal
                open={Boolean(deleting)}
                tone="red"
                fullScreen={false}
                title="Hapus Artikel"
                onClose={() => setDeleting(null)}
                footer={
                    <>
                        <button type="button" onClick={() => setDeleting(null)} className="rounded-lg border border-slate-300 bg-white px-5 py-2 hover:bg-slate-100">Batal</button>
                        <button type="button" onClick={confirmDelete} disabled={deleteBusy} className="flex items-center gap-2 rounded-lg bg-red-600 px-5 py-2 font-medium text-white hover:bg-red-700">{deleteBusy && <Spinner />} Hapus</button>
                    </>
                }
            >
                <p>Yakin ingin menghapus <strong>{deleting?.title}</strong>? Tindakan ini tidak dapat dibatalkan.</p>
            </Modal>
        </div>
    );
}
