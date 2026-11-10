import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import React, { useState } from'react';import{Head,Link,router}from'@inertiajs/react';import AppLayout from'../../Layouts/AppLayout';
function MarkdownContent({ content }) {
  const host = React.useRef(null);
  React.useEffect(() => {
    if (!host.current) return;
    const viewer = Editor.factory({ el: host.current, viewer: true, initialValue: content || '', usageStatistics: false });
    return () => viewer.destroy();
  }, [content]);
  return <div ref={host} className="prose-blog mt-8" />;
}
export default function Show({post}){const [cover,setCover]=useState(null);const [busy,setBusy]=useState(false);return <AppLayout><Head title={post.title}/><div className="max-w-4xl mx-auto p-6"><Link href="/dashboard/posts" className="text-blue-700">← Kembali ke artikel</Link><p className="mt-8 text-sm text-blue-700">{post.category} · {post.is_published?'Published':'Draft'}</p><h1 className="text-4xl font-bold mt-3">{post.title}</h1>{post.cover_image_path&&<img src={`/storage/${post.cover_image_path}`} alt={`Sampul ${post.title}`} className="w-full rounded-xl mt-6"/>}
<div className="surface p-5 mt-6"><h2 className="font-bold">Foto sampul</h2><form className="flex flex-wrap gap-3 items-center mt-3" onSubmit={e=>{e.preventDefault();if(!cover)return;const data=new FormData();data.append('cover',cover);setBusy(true);router.post(`/dashboard/posts/${post.id}/cover`,data,{forceFormData:true,onFinish:()=>setBusy(false),onSuccess:()=>setCover(null)});}}><label className="text-sm">Pilih gambar (JPG, PNG, WEBP, maks. 5 MB)<input type="file" accept="image/jpeg,image/png,image/webp" onChange={e=>setCover(e.target.files?.[0]||null)} className="block mt-2"/></label><button disabled={!cover||busy} className="bg-blue-600 text-white rounded-lg px-4 py-2 disabled:opacity-50">{busy?'Mengunggah...':'Unggah sampul'}</button>{post.cover_image_path&&<button type="button" onClick={()=>router.delete(`/dashboard/posts/${post.id}/cover`)} className="border border-rose-300 text-rose-700 rounded-lg px-4 py-2">Hapus sampul</button>}</form></div><MarkdownContent content={post.content} /></div></AppLayout>}
