import { Head, Link, useForm } from '@inertiajs/react';
import Spinner from '../../Components/Spinner';

const Field = ({ id, label, type = 'text', value, onChange, error }) => (
    <div>
        <label htmlFor={id} className="mb-1 block text-sm font-medium">{label}</label>
        <input id={id} type={type} value={value} onChange={onChange} className="w-full rounded-lg border border-slate-300 px-3 py-2 focus:border-indigo-500 focus:outline-none" />
        {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
);

export default function Register() {
    const { data, setData, post, processing, errors } = useForm({ name: '', username: '', email: '', password: '', password_confirmation: '' });

    const submit = (e) => {
        e.preventDefault();
        post('/register');
    };

    return (
        <div className="mx-auto max-w-md rounded-2xl border border-slate-200 bg-white p-8 shadow-sm">
            <Head title="Daftar" />
            <h1 className="text-2xl font-bold">Daftar Penulis</h1>
            <p className="mt-1 text-sm text-slate-500">Bergabung dan mulai menerbitkan artikel Anda.</p>
            <form onSubmit={submit} className="mt-6 space-y-4">
                <Field id="register-name" label="Nama lengkap" value={data.name} onChange={(e) => setData('name', e.target.value)} error={errors.name} />
                <Field id="register-username" label="Username" value={data.username} onChange={(e) => setData('username', e.target.value)} error={errors.username} />
                <Field id="register-email" label="Email" type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} error={errors.email} />
                <Field id="register-password" label="Password" type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} error={errors.password} />
                <Field id="register-password-confirmation" label="Konfirmasi password" type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} />
                <button id="register-submit" type="submit" disabled={processing} className="flex w-full items-center justify-center gap-2 rounded-lg bg-indigo-600 py-2.5 font-medium text-white hover:bg-indigo-700">
                    {processing && <Spinner />} Daftar
                </button>
            </form>
            <p className="mt-6 text-center text-sm text-slate-500">Sudah punya akun? <Link href="/login" className="font-medium text-indigo-600">Masuk</Link></p>
        </div>
    );
}
