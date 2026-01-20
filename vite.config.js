import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/sass/app.scss',
                'resources/js/app.js',
                'resources/js/vendor.js',
                // Components
                'resources/js/components/timepicker.js',
                'resources/js/components/dropzone.js',
                'resources/js/components/input-spin.js',
                // Pages
                'resources/js/pages/apps-calendar.js',
                'resources/js/pages/apps-ecommerce-orders.js',
                'resources/js/pages/apps-mailbox.js',
                'resources/js/pages/dashboard-analytics.js',
                'resources/js/pages/dashboard-ecommerce.js',
                'resources/js/pages/dashboard-email.js',
                'resources/js/pages/dashboard-hr.js',
                'resources/js/pages/landing.js',
                'resources/js/pages/pages-coming-soon.js',
            ],
            refresh: true,
        }),
    ],
    build: {
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                manualChunks: {
                    'fullcalendar': ['@fullcalendar/core', '@fullcalendar/daygrid', '@fullcalendar/list', '@fullcalendar/timegrid'],
                    'charts': ['apexcharts'],
                    'ui-libs': ['dropzone', 'flatpickr', 'simplebar', 'swiper'],
                },
            },
        },
    },
});
