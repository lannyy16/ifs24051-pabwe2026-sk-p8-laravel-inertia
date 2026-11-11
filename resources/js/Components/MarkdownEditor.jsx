import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';
import { useEffect, useRef } from 'react';

/** TOAST UI Editor (Markdown). `value` hanya dipakai sebagai nilai awal. */
export default function MarkdownEditor({ value = '', onChange, height = '420px' }) {
    const el = useRef(null);

    useEffect(() => {
        const editor = new Editor({
            el: el.current,
            height,
            initialEditType: 'markdown',
            previewStyle: 'vertical',
            initialValue: value,
            usageStatistics: false,
            events: { change: () => onChange?.(editor.getMarkdown()) },
        });
        return () => editor.destroy();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return <div ref={el} />;
}
