import tailwindcss from '@tailwindcss/vite';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/auth.js', 'resources/js/chat.js', 'resources/js/profile.js', 'resources/js/setup-profile.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
