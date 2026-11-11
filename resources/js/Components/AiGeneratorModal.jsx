import { router } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';
import Modal from './Modal';
import Spinner from './Spinner';

export default function AiGeneratorModal({ open, categories, onClose }) {
    const [topic, setTopic] = useState('');
    const [count, setCount] = useState(1);
    const [category, setCategory] = useState('');
    const [publish, setPublish] = useState(false);
    const [items, setItems] = useState([]);
    const [error, setError] = useState('');
    const [generating, setGenerating] = useState(false);
    const [saving, setSaving] = useState(false);

    const generate = async () => {
        setError('');
        setGenerating(true);
        try {
            const { data } = await axios.post('/dashboard/posts/ai-generate', { topic, count: Number(count), category: category || null });
            setItems(data.items);
        } catch (e) {
            setError(e.response?.data?.message ?? 'Gagal menghasilkan artikel.');
        } finally {
            setGenerating(false);
        }
    };

    const save = () => {
        router.post('/dashboard/posts/ai-save', { items, publish }, {
            preserveScroll: true,
            onStart: () => setSaving(true),
            onFinish: () => setSaving(false),
            onSuccess: () => { setItems([]); setTopic(''); onClose(); },
        });
    };

    const input = 'w-full rounded-lg border border-slate-300 px-3 py-2';

    return (
        <Modal
            open={open}
            tone="violet"
            title="Rancang Artikel dengan AI"
            onClose={onClose}
            footer={
                <>
                    <button type="button" onClick={onClose} className="rounded-lg border border-slate-300 bg-white px-5 py-2 hover:bg-slate-100">Batal</button>
                    <button type="button" onClick={save} disabled={saving || items.length === 0} className="flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 font-medium text-white hover:bg-violet-700">
                        {saving && <Spinner />} Simpan {items.length > 0 ? `${items.length} Artikel` : ''}
                    </button>
                </>
            }
        >
            <div className="mx-auto max-w-3xl space-y-5">
                <div>
                    <label htmlFor="ai-topic" className="mb-1 block text-sm font-medium">Topik</label>
                    <input id="ai-topic" className={input} value={topic} onChange={(e) => setTopic(e.target.value)} placeholder="mis. Tips produktif bekerja dari rumah" />
                </div>
                <div className="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label htmlFor="ai-count" className="mb-1 block text-sm font-medium">Jumlah artikel (1–5)</label>
                        <input id="ai-count" type="number" min={1} max={5} className={input} value={count} onChange={(e) => setCount(Math.min(5, Math.max(1, Number(e.target.value) || 1)))} />
                    </div>
                    <div>
                        <label htmlFor="ai-category" className="mb-1 block text-sm font-medium">Kategori (opsional)</label>
                        <select id="ai-category" className={input} value={category} onChange={(e) => setCategory(e.target.value)}>
                            <option value="">Otomatis</option>
                            {categories.map((c) => <option key={c.value} value={c.value}>{c.label}</option>)}
                        </select>
                    </div>
                </div>
                <button type="button" onClick={generate} disabled={generating || topic.trim() === ''} className="flex items-center gap-2 rounded-lg bg-violet-600 px-5 py-2 font-medium text-white hover:bg-violet-700">
                    {generating && <Spinner />} {generating ? 'Menghasilkan...' : 'Hasilkan Artikel'}
                </button>
                {error && <p className="rounded-lg bg-red-50 p-3 text-sm text-red-700">{error}</p>}

                {items.length > 0 && (
                    <div className="space-y-3">
                        <label className="flex items-center gap-2 text-sm"><input type="checkbox" checked={publish} onChange={(e) => setPublish(e.target.checked)} /> Langsung publikasikan</label>
                        {items.map((it, i) => (
                            <div key={i} className="rounded-xl border border-violet-200 bg-violet-50/50 p-4">
                                <span className="rounded-full bg-violet-100 px-2 py-0.5 text-xs text-violet-700">{it.category}</span>
                                <h3 className="mt-2 font-semibold">{it.title}</h3>
                                {it.excerpt && <p className="mt-1 text-sm text-slate-600">{it.excerpt}</p>}
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </Modal>
    );
}
