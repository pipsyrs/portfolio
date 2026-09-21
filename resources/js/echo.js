import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

/**
 * Echo hanya dibuat bila Reverb memang dikonfigurasi. Tanpa penjagaan ini,
 * pusher-js terus mencoba menyambung ke host kosong di setiap halaman dan
 * membanjiri console — padahal antarmuka sudah punya polling sebagai cadangan.
 */
const key = import.meta.env.VITE_REVERB_APP_KEY;

if (key) {
    window.Pusher = Pusher;

    window.Echo = new Echo({
        broadcaster: 'reverb',
        key,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT ?? 80,
        wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
        wsPath: import.meta.env.VITE_REVERB_PATH ?? '',
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
    });
}
