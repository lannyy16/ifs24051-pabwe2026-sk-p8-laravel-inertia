
import { useEffect, useRef } from 'react';

/** TOAST UI Editor (Markdown), dimuat hanya ketika komponen digunakan. */
export default function MarkdownEditor({
    value = '',
    onChange,
    height = '420px',
}) {
    const el = useRef(null);
    const editorRef = useRef(null);
    const valueRef = useRef(value);
    const onChangeRef = useRef(onChange);

    // Selalu gunakan nilai dan callback terbaru.
    valueRef.current = value;
    onChangeRef.current = onChange;

    useEffect(() => {
        let cancelled = false;
        let editor = null;

        const initializeEditor = async () => {
            const [{ default: Editor }] = await Promise.all([
                import('@toast-ui/editor'),
                import('@toast-ui/editor/dist/toastui-editor.css'),
            ]);

            if (cancelled || !el.current) return;

            editor = new Editor({
                el: el.current,
                height,
                initialEditType: 'markdown',
                previewStyle: 'vertical',
                initialValue: valueRef.current,
                usageStatistics: false,
                events: {
                    change: () => {
                        onChangeRef.current?.(editor.getMarkdown());
                    },
                },
            });

            editorRef.current = editor;
        };

        initializeEditor().catch((error) => {
            console.error('Gagal memuat Markdown Editor:', error);
        });

        return () => {
            cancelled = true;
            editor?.destroy();
            editorRef.current = null;
        };
    }, []);

    return <div ref={el} />;
}
