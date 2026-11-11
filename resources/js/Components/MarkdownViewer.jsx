import Viewer from '@toast-ui/editor/dist/toastui-editor-viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import { useEffect, useRef } from 'react';

/** TOAST UI Viewer untuk merender Markdown. */
export default function MarkdownViewer({ content = '' }) {
    const el = useRef(null);

    useEffect(() => {
        const viewer = new Viewer({ el: el.current, initialValue: content, usageStatistics: false });
        return () => viewer.destroy();
    }, [content]);

    return <div ref={el} className="markdown-viewer" />;
}
