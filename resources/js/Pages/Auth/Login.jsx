import { Head, Link, useForm } from '@inertiajs/react';
import Spinner from '../../Components/Spinner';

export default function Login() {
    const { data, setData, post, processing, errors } = useForm({ login: '', password: '', remember: false });

    const submit = (e) => {
        e.preventDefault();
        post('/login');
    };

    return (
        <div className="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <Head title="Masuk" />
            <h1 className="text-2xl font-bold">Masuk</h1>
            <p className="mt-1 text-sm text-slate-500">Masuk untuk menulis dan mengelola artikel Anda.</p>

            <form onSubmit={submit} className="mt-6 space-y-4">
                <div>
                    <label htmlFor="login-username" className="mb-1 block text-sm font-medium">Username atau Email</label>
                    <input id="login-username" name="login" type="text" autoComplete="username" value={data.login} onChange={(e) => setData('login', e.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none" />
                    {errors.login && <p className="mt-1 text-sm text-red-600">{errors.login}</p>}
                </div>
                <div>
                    <label htmlFor="login-password" className="mb-1 block text-sm font-medium">Password</label>
                    <input id="login-password" name="password" type="password" autoComplete="current-password" value={data.password} onChange={(e) => setData('password', e.target.value)} className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none" />
                    {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                </div>
                <label className="flex items-center gap-2 text-sm">
                    <input type="checkbox" checked={data.remember} onChange={(e) => setData('remember', e.target.checked)} /> Ingat saya
                </label>
                <button id="login-submit" type="submit" disabled={processing} className="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2.5 font-medium text-white hover:bg-indigo-700">
                    {processing && <Spinner />} Masuk
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-500">Belum punya akun? <Link href="/register" className="font-medium text-indigo-600">Daftar sebagai penulis</Link></p>
        </div>
    );
}
