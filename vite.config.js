
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.jsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],

    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) {
                        return;
                    }

                    if (
                        id.includes('@toast-ui/editor') ||
                        id.includes('tui-code-snippet') ||
                        id.includes('codemirror')
                    ) {
                        return 'editor-vendor';
                    }

                    if (id.includes('@inertiajs')) {
                        return 'inertia-vendor';
                    }

                    if (
                        id.includes('react') ||
                        id.includes('react-dom') ||
                        id.includes('scheduler')
                    ) {
                        return 'react-vendor';
                    }

                    if (id.includes('lucide-react')) {
                        return 'icons-vendor';
                    }
                },
            },
        },
    },
});
