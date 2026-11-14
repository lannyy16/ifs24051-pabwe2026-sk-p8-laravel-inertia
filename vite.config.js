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
        manifest: true,
        assetsDir: 'assets',
        sourcemap: false,
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes('node_modules')) return;
                    if (/node_modules\/(react|react-dom|scheduler)\//.test(id)) return 'react-vendor';
                    if (/node_modules\/@inertiajs\//.test(id)) return 'inertia-vendor';
                },
            },
        },
    },
});