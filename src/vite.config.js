import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                `resources/css/filament/admin/theme.css`,
                "resources/css/pwa-ui.css",
                "resources/js/pwa-install.js",
                "resources/js/pwa-sw-register.js",
            ],
            refresh: true,
        }),
    ],
    
    // Docker configuration - allow external access
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: false,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: process.env.VITE_HMR_PORT || 5173,
        },
    },

    // build: {
    //     chunkSizeWarningLimit: 500,
    //     cssCodeSplit: true,
    //     reportCompressedSize: false,
    //     rollupOptions: {
    //         output: {
    //             manualChunks(id) {
    //                 if (id.includes("node_modules")) {
    //                     return id
    //                         .toString()
    //                         .split("node_modules/")[1]
    //                         .split("/")[0]
    //                         .toString();
    //                 }
    //             },
    //         },
    //     },
    // },
});
