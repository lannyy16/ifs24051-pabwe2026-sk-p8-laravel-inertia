
import { useEffect, useRef } from 'react';

/** TOAST UI Viewer, dimuat hanya ketika komponen digunakan. */
export default function MarkdownViewer({ content = '' }) {
    const el = useRef(null);

    useEffect(() => {
        let cancelled = false;
        let viewer = null;

        const initializeViewer = async () => {
            const [{ default: Viewer }] = await Promise.all([
                import('@toast-ui/editor/dist/toastui-editor-viewer'),
                import('@toast-ui/editor/dist/toastui-editor-viewer.css'),
            ]);

            if (cancelled || !el.current) return;

            viewer = new Viewer({
                el: el.current,
                initialValue: content,
                usageStatistics: false,
            });
        };

        initializeViewer().catch((error) => {
            console.error('Gagal memuat Markdown Viewer:', error);
        });

        return () => {
            cancelled = true;
            viewer?.destroy();
        };
    }, [content]);

    return <div ref={el} className="markdown-viewer" />;
}
