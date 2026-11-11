import { CloseIcon } from './Icons';

const tones = {
    indigo: { header: 'bg-indigo-600 text-white', footer: 'bg-indigo-50 border-indigo-100' },
    amber: { header: 'bg-amber-500 text-white', footer: 'bg-amber-50 border-amber-100' },
    violet: { header: 'bg-violet-600 text-white', footer: 'bg-violet-50 border-violet-100' },
    red: { header: 'bg-red-600 text-white', footer: 'bg-red-50 border-red-100' },
};

/** Modal Full Screen: header & footer berwarna kontras, tombol aksi di kanan footer. */
export default function Modal({ open, title, tone = 'indigo', fullScreen = true, onClose, footer, children }) {
    if (!open) return null;
    const t = tones[tone] ?? tones.indigo;

    return (
        <div className={`fixed inset-0 z-50 flex ${fullScreen ? '' : 'items-center justify-center bg-black/40 p-4'}`} role="dialog" aria-modal="true">
            <div className={`flex flex-col bg-white ${fullScreen ? 'h-full w-full' : 'max-h-full w-full max-w-md overflow-hidden rounded-xl shadow-xl'}`}>
                <header className={`flex items-center justify-between px-6 py-4 ${t.header}`}>
                    <h2 className="text-lg font-semibold">{title}</h2>
                    <button type="button" onClick={onClose} aria-label="Tutup" className="rounded p-1 hover:bg-white/20"><CloseIcon /></button>
                </header>
                <div className="flex-1 overflow-y-auto p-6">{children}</div>
                <footer className={`flex items-center justify-end gap-3 border-t px-6 py-4 ${t.footer}`}>{footer}</footer>
            </div>
        </div>
    );
}
