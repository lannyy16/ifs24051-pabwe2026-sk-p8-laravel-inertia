import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import React from'react';import{Head,Link}from'@inertiajs/react';import AppLayout from'../../Layouts/AppLayout';import{ArrowLeft,UserPlus}from'lucide-react';
function MarkdownContent({ content }) {
  const host = React.useRef(null);
  React.useEffect(() => {
    if (!host.current) return;
    const viewer = Editor.factory({ viewer: true, el: host.current, initialValue: content || '', usageStatistics: false });
    return () => viewer.destroy();
  }, [content]);
  return <div ref={host} className="prose-blog mt-8" />;
}
export default function PostShow({post}){return <AppLayout publicPage><Head title={post.title}/><article className="max-w-4xl mx-auto px-5 py-12"><Link href="/articles" className="inline-flex gap-2 text-blue-700 font-semibold"><ArrowLeft size={18}/>Semua artikel</Link><p className="mt-10 text-blue-700 text-sm font-bold">{post.category}</p><h1 className="text-4xl md:text-5xl font-bold leading-tight mt-3">{post.title}</h1><p className="text-slate-500 mt-4">Ditulis oleh {post.user?.name} · {post.published_at?.slice(0,10)}</p>{post.cover_image_path&&<img src={`/storage/${post.cover_image_path}`} className="w-full max-h-[480px] object-cover rounded-2xl mt-8"/>}<p className="text-xl text-slate-600 mt-8">{post.excerpt}</p><MarkdownContent content={post.content} /><div className="surface p-7 mt-14 text-center"><h2 className="text-2xl font-bold">Punya cerita untuk dibagikan?</h2><p className="text-slate-600 mt-2">Bergabunglah dengan komunitas penulis BlogVerse.</p><div className="flex justify-center gap-3 mt-5"><Link href="/login" className="bg-blue-600 text-white rounded-lg px-5 py-3">Login</Link><Link href="/register" className="border rounded-lg px-5 py-3 flex gap-2"><UserPlus size={18}/>Daftar</Link></div></div></article></AppLayout>}
