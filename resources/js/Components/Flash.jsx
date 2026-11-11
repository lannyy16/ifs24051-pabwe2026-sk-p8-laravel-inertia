import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function Flash() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(true);
    const message = flash?.success || flash?.error;

    useEffect(() => {
        setVisible(true);
        const t = setTimeout(() => setVisible(false), 5000);
        return () => clearTimeout(t);
    }, [flash?.success, flash?.error]);

    if (!message || !visible) return null;

    const ok = Boolean(flash.success);
    return (
        <div role="status" className={`mb-4 flex items-start justify-between rounded-lg border px-4 py-3 text-sm ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'}`}>
            <span>{message}</span>
            <button type="button" onClick={() => setVisible(false)} className="ml-4 font-bold" aria-label="Tutup">×</button>
        </div>
    );
}
