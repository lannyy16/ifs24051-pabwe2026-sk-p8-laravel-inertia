import './bootstrap';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';
import AppLayout from './Layouts/AppLayout';

const pages = import.meta.glob('./Pages/**/*.jsx', { eager: true });

createInertiaApp({
    progress: false,
    title: (title) => (title ? `${title} · BlogVerse Pro` : 'BlogVerse Pro'),
    resolve: (name) => {
        const page = pages[`./Pages/${name}.jsx`];
        page.default.layout ??= (node) => <AppLayout>{node}</AppLayout>;
        return page;
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
