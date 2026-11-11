import { useEffect, useRef, useState } from 'react';
import { Link } from '@inertiajs/react';
import { DotsIcon, EyeIcon, PencilIcon, TrashIcon } from './Icons';

/** Menu tindakan (⋮): Detail, Ubah, Hapus. */
export default function KebabMenu({ detailHref, onEdit, onDelete }) {
    const [open, setOpen] = useState(false);
    const ref = useRef(null);

    useEffect(() => {
        const close = (e) => {
            if (ref.current && !ref.current.contains(e.target)) setOpen(false);
        };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, []);

    const item = 'flex w-full items-center gap-2 px-4 py-2 text-left text-sm hover:bg-slate-50';

    return (
        <div className="relative" ref={ref}>
            <button type="button" aria-label="Menu tindakan" onClick={() => setOpen((v) => !v)} className="rounded-full p-2 text-slate-500 hover:bg-slate-100">
                <DotsIcon />
            </button>
            {open && (
                <div className="absolute right-0 z-20 mt-1 w-40 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <Link href={detailHref} className={`${item} text-indigo-600`}><EyeIcon /> Detail</Link>
                    <button type="button" onClick={() => { setOpen(false); onEdit(); }} className={`${item} text-indigo-600`}><PencilIcon /> Ubah</button>
                    <button type="button" onClick={() => { setOpen(false); onDelete(); }} className={`${item} text-red-600`}><TrashIcon /> Hapus</button>
                </div>
            )}
        </div>
    );
}
