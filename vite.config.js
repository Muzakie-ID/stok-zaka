import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig(({ mode }) => {
    // 1. Load semua variabel dari .env
    // Parameter ketiga '' (string kosong) artinya: "Baca semua variabel, jangan cuma yang awalan VITE_"
    const env = loadEnv(mode, process.cwd(), '');

    return {
        // 2. Gunakan logic dinamis:
        // Jika APP_URL ada di .env, pakai itu ditambah '/build/'.
        // Jika tidak ada, pakai default '/build/' saja.
        base: env.APP_URL ? `${env.APP_URL}/build/` : '/build/',

        plugins: [
            laravel({
                input: ['resources/css/app.css', 'resources/js/app.js', 'resources/js/niimbot.js'],
                refresh: true,
            }),
            tailwindcss(),
        ],
        build: {
            manifest: 'manifest.json',
            outDir: 'public/build',
            rollupOptions: {
                output: {
                    manualChunks: undefined,
                },
            },
        },
        server: {
            watch: {
                ignored: ['**/storage/framework/views/**'],
            },
        },
    };
});
