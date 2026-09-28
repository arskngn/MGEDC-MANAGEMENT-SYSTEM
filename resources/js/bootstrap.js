import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allow your team to quickly build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

function initEcho() {
    const config = window.__REVERB_CONFIG__ || {};
    const key = config.key || import.meta.env.VITE_REVERB_APP_KEY || 'lxh3qzvrykrfuhwp1nnf';
    
    let rawHost = config.host || import.meta.env.VITE_REVERB_HOST || '';
    // If the host is empty, or points to the web app instead of reverb, point directly to Reverb
    if (!rawHost || rawHost.includes('ge-system-11-production.up.railway.app') || rawHost === window.location.hostname) {
        rawHost = 'reverb-production-a38d.up.railway.app';
    }
    
    const wsHost = rawHost.replace(/^https?:\/\//, '').replace(/^wss?:\/\//, '').split(':')[0];
    const isHttps = true; // Railway always uses HTTPS/WSS
    const wsPort = 443;

    if (key && !window.Echo) {
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: key,
            wsHost: wsHost,
            wsPort: wsPort,
            wssPort: wsPort,
            forceTLS: isHttps,
            enabledTransports: ['ws', 'wss'],
            disableStats: true,
            authEndpoint: '/broadcasting/auth',
            auth: {
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                }
            }
        });
    }
}

// Initialize Echo immediately if config exists, or on window load
initEcho();
window.initEcho = initEcho;


