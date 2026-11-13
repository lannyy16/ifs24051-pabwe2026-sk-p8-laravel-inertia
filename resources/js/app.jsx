// JavaScript / JSX
import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';

const pages = import.meta.glob('./Pages/**/*.jsx');

createInertiaApp({
    resolve: async (name) => {
        const path = `./Pages/${name}.jsx`;
        const pageLoader = pages[path];

        if (!pageLoader) {
            throw new Error(`Halaman Inertia tidak ditemukan: ${name}`);
        }

        const page = await pageLoader();

        return page.default;
    },

    setup({ el, App, props }) {
        createRoot(el).render(
            <React.StrictMode>
                <App {...props} />
            </React.StrictMode>
        );
    },

    progress: {
        color: '#2563eb',
        showSpinner: false,
    },
});