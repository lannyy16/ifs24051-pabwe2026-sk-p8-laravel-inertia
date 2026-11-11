import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Spinner from './Spinner';

/** Top progress bar + floating pill "Memproses tindakan..." untuk setiap request Inertia. */
export default function GlobalLoading() {
    const [busy, setBusy] = useState(false);

    useEffect(() => {
        const offStart = router.on('start', () => setBusy(true));
        const offFinish = router.on('finish', () => setBusy(false));
        return () => {
            offStart();
            offFinish();
        };
    }, []);

    if (!busy) return null;

    return (
        <>
            <div className="fixed inset-x-0 top-0 z-[100] h-1 overflow-hidden bg-indigo-100">
                <div className="animate-topbar h-full w-1/2 rounded-full bg-indigo-600" />
            </div>
            <div className="fixed bottom-5 right-5 z-[100] flex items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm text-white shadow-lg">
                <Spinner /> Memproses tindakan...
            </div>
        </>
    );
}
