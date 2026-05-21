import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/dashboard.css',
                'resources/css/employee-create.css',
                'resources/css/fixedstyles.css',
                'resources/css/modifiedstyles.css',
                'resources/css/order-numbers.css',
                'resources/css/dark-mode.css',
                'resources/css/settings-panel.css',
                'resources/css/theme-variables.css',
                'resources/js/app.js',
                'resources/js/payments.js',
                'resources/js/employee-balance.js',
                'resources/js/deductions.js',
                'resources/js/employee-statuses.js',
                'resources/js/airtime.js',
                'resources/css/salary-app.css',
                'resources/js/salary-app.js',
                'resources/css/sidebar.css',
                'resources/js/sidebar.js',
                'resources/css/salary-role-manager.css',
                'resources/js/salary-role-manager.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
