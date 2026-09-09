/**
 * Inisialisasi Laravel Echo (Reverb) secara aman — PRD pasal 13.
 * - Tidak merusak build jika package belum di-install: gunakan dynamic import + try/catch.
 * - Tidak merusak runtime jika ENV belum diset: Echo hanya dibuat bila VITE_REVERB_* tersedia.
 * - Mengekspos window.Echo + window.__echoStatus ('connected' | 'disconnected' | 'disabled').
 */
export async function initEcho() {
    if (window.Echo) return window.Echo;

    window.__echoStatus = 'disabled';

    const key = import.meta.env.VITE_REVERB_APP_KEY;
    const host = import.meta.env.VITE_REVERB_HOST ?? '127.0.0.1';
    const port = import.meta.env.VITE_REVERB_PORT ?? '8080';
    const scheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

    if (!key) {
        console.info('[echo] VITE_REVERB_APP_KEY belum diset — realtime dinonaktifkan, fallback polling dipakai.');
        return null;
    }

    try {
        const [{ default: Echo }, { default: Pusher }] = await Promise.all([
            import('laravel-echo'),
            import('pusher-js'),
        ]);
        window.Pusher = window.Pusher ?? Pusher;
        const echo = new Echo({
            broadcaster: 'reverb',
            key,
            wsHost: host,
            wsPort: Number(port),
            wssPort: Number(port),
            forceTLS: scheme === 'https',
            enabledTransports: ['ws', 'wss'],
        });
        window.Echo = echo;
        window.__echoStatus = 'connected';
        try {
            const conn = echo.connector?.pusher?.connection;
            conn?.bind?.('connected', () => { window.__echoStatus = 'connected'; window.dispatchEvent(new Event('echo:connected')); });
            conn?.bind?.('disconnected', () => { window.__echoStatus = 'disconnected'; window.dispatchEvent(new Event('echo:disconnected')); });
            conn?.bind?.('failed', () => { window.__echoStatus = 'disconnected'; window.dispatchEvent(new Event('echo:disconnected')); });
        } catch { /* abaikan */ }
        window.dispatchEvent(new Event('echo:connected'));
        return echo;
    } catch (e) {
        console.warn('[echo] laravel-echo/pusher-js belum ter-install. Jalankan: npm install — fallback polling dipakai.', e?.message ?? e);
        window.__echoStatus = 'disabled';
        return null;
    }
}

export function echoChannel(name) {
    return window.Echo ? window.Echo.channel(name) : null;
}
