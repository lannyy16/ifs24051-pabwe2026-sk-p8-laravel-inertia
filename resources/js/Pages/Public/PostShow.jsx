import { Head, Link, usePage } from '@inertiajs/react';
import MarkdownViewer from '../../Components/MarkdownViewer';
import { LoginIcon, UserPlusIcon } from '../../Components/Icons';
import { formatDate } from './PostIndex';

export default function PostShow({ post, related }) {
    const { auth } = usePage().props;

    return (
        <article className="mx-auto max-w-3xl">
            <Head title={post.title} />
            <Link href="/articles" className="text-sm text-indigo-600">← Semua artikel</Link>
            <span className="mt-4 block w-fit rounded-full bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-700">{post.category}</span>
            <h1 className="mt-3 font-serif text-3xl font-extrabold leading-tight sm:text-4xl">{post.title}</h1>
            <p className="mt-3 text-sm text-slate-500">Oleh <strong>{post.user?.name}</strong> · {formatDate(post.published_at)}</p>
            {post.cover_url && <img src={post.cover_url} alt={post.title} className="mt-6 w-full rounded-2xl object-cover" />}
            {post.excerpt && <p className="mt-6 border-l-4 border-indigo-400 pl-4 text-lg italic text-slate-600">{post.excerpt}</p>}
            <div className="mt-6"><MarkdownViewer content={post.content} /></div>

            {!auth.user && (
                <section className="mt-12 rounded-2xl bg-indigo-50 p-6 text-center">
                    <h2 className="text-xl font-bold text-indigo-900">Suka tulisan seperti ini?</h2>
                    <p className="mt-1 text-sm text-indigo-800">Bergabung dengan komunitas dan bagikan ide Anda sendiri.</p>
                    <div className="mt-4 flex justify-center gap-3">
                        <Link href="/login" className="flex items-center gap-2 rounded-lg bg-white px-4 py-2 font-medium text-indigo-700 shadow-sm"><LoginIcon /> Masuk</Link>
                        <Link href="/register" className="flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white"><UserPlusIcon /> Daftar</Link>
                    </div>
                </section>
            )}

            {related.length > 0 && (
                <section className="mt-12">
                    <h2 className="mb-4 text-xl font-bold">Baca juga</h2>
                    <ul className="space-y-3">
                        {related.map((r) => (
                            <li key={r.id}><Link href={`/articles/${r.slug}`} className="block rounded-lg border border-slate-200 bg-white p-4 hover:border-indigo-300"><span className="font-medium">{r.title}</span><span className="ml-2 text-xs text-slate-500">{r.category}</span></Link></li>
                        ))}
                    </ul>
                </section>
            )}
        </article>
    );
}
