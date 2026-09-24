import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                // Palet resmi Kementerian PPN/Bappenas, diambil dari logo lembaga
                // (lihat public/images/logo-bappenas.png) -- menggantikan skala
                // `blue` default Tailwind agar seluruh kelas `bg-blue-*`/`text-blue-*`
                // yang sudah dipakai di aplikasi otomatis mengikuti warna korporat,
                // tanpa perlu mengubah tiap file Blade satu per satu.
                blue: {
                    50: '#eef4fa',
                    100: '#d7e5f2',
                    200: '#b0cce6',
                    300: '#82add4',
                    400: '#548fbf',
                    500: '#3e76a5',
                    600: '#346698', // biru navy utama logo Bappenas
                    700: '#2a5480',
                    800: '#1f4066', // dipakai tombol/aksen utama (dahulu blue-800)
                    900: '#16304f', // dipakai header/hero gelap (dahulu blue-900)
                    950: '#0d1f35',
                },
                // Aksen emas/kuning tua dari logo (elemen "P" ketiga) -- dipakai
                // terbatas untuk penanda status aktif/highlight, bukan warna utama.
                gold: {
                    50: '#fbf3e4',
                    100: '#f5e4c0',
                    200: '#ecd08d',
                    300: '#dfb65c',
                    400: '#d5a545',
                    500: '#ca9934',
                    600: '#b0812a',
                    700: '#8c6621',
                    800: '#6b4e1a',
                    900: '#4a3612',
                },
            },
        },
    },

    plugins: [forms],
};
