import { Head, router, useForm } from '@inertiajs/react';
import { useRef } from 'react';
import Spinner from '../../Components/Spinner';

const input = 'w-full rounded-lg border border-slate-300 px-3 py-2';
const Err = ({ msg }) => (msg ? <p className="mt-1 text-sm text-red-600">{msg}</p> : null);
const Card = ({ title, children }) => (
    <section className="rounded-xl border border-slate-200 bg-white p-6">
        <h2 className="mb-4 text-lg font-semibold">{title}</h2>
        {children}
    </section>
);
const Submit = ({ processing, children }) => (
    <button type="submit" disabled={processing} className="flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2 font-medium text-white hover:bg-indigo-700">
        {processing && <Spinner />} {children}
    </button>
);

export default function Edit({ user, postCount }) {
    const identity = useForm({ name: user.name, username: user.username, email: user.email });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });
    const fileInput = useRef(null);

    const uploadPhoto = (e) => {
        const photo = e.target.files[0];
        if (photo) router.post('/profile/photo', { photo }, { forceFormData: true, preserveScroll: true });
        e.target.value = '';
    };

    return (
        <div className="mx-auto max-w-xl space-y-6">
            <Head title="Profil" />
            <div>
                <h1 className="text-2xl font-bold">Profil Penulis</h1>
                <p className="mt-1 text-sm text-slate-500">Anda telah menulis {postCount} artikel.</p>
            </div>

            <Card title="Foto Profil">
                <div className="flex items-center gap-4">
                    {user.profile_photo_url ? (
                        <img src={user.profile_photo_url} alt="" className="h-20 w-20 rounded-full object-cover" />
                    ) : (
                        <span className="flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-2xl font-bold text-indigo-600">{user.name.charAt(0).toUpperCase()}</span>
                    )}
                    <div className="flex gap-2">
                        <button type="button" onClick={() => fileInput.current.click()} className="rounded-lg border border-slate-300 px-4 py-2 text-sm hover:bg-slate-100">Unggah Foto</button>
                        <input ref={fileInput} type="file" accept="image/*" className="hidden" onChange={uploadPhoto} />
                        {user.profile_photo_path && (
                            <button type="button" onClick={() => router.delete('/profile/photo', { preserveScroll: true })} className="rounded-lg border border-red-200 px-4 py-2 text-sm text-red-600 hover:bg-red-50">Hapus</button>
                        )}
                    </div>
                </div>
            </Card>

            <Card title="Identitas">
                <form onSubmit={(e) => { e.preventDefault(); identity.patch('/profile', { preserveScroll: true }); }} className="space-y-4">
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-name">Nama</label><input id="p-name" className={input} value={identity.data.name} onChange={(e) => identity.setData('name', e.target.value)} /><Err msg={identity.errors.name} /></div>
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-username">Username</label><input id="p-username" className={input} value={identity.data.username} onChange={(e) => identity.setData('username', e.target.value)} /><Err msg={identity.errors.username} /></div>
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-email">Email</label><input id="p-email" type="email" className={input} value={identity.data.email} onChange={(e) => identity.setData('email', e.target.value)} /><Err msg={identity.errors.email} /></div>
                    <Submit processing={identity.processing}>Simpan Identitas</Submit>
                </form>
            </Card>

            <Card title="Ubah Password">
                <form onSubmit={(e) => { e.preventDefault(); password.put('/profile/password', { preserveScroll: true, onSuccess: () => password.reset() }); }} className="space-y-4">
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-current">Password saat ini</label><input id="p-current" type="password" className={input} value={password.data.current_password} onChange={(e) => password.setData('current_password', e.target.value)} /><Err msg={password.errors.current_password} /></div>
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-pass">Password baru</label><input id="p-pass" type="password" className={input} value={password.data.password} onChange={(e) => password.setData('password', e.target.value)} /><Err msg={password.errors.password} /></div>
                    <div><label className="mb-1 block text-sm font-medium" htmlFor="p-pass2">Konfirmasi password baru</label><input id="p-pass2" type="password" className={input} value={password.data.password_confirmation} onChange={(e) => password.setData('password_confirmation', e.target.value)} /></div>
                    <Submit processing={password.processing}>Ubah Password</Submit>
                </form>
            </Card>
        </div>
    );
}
