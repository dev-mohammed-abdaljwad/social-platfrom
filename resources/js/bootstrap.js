import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

/**
 * Echo exposes an expressive API for subscribing to channels and listening
 * for events that are broadcast by Laravel. Echo and event broadcasting
 * allows your team to easily build robust real-time web applications.
 */

import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

// Echo will be initialized from the layout when user is authenticated
window.initializeEcho = function(config) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: config.key,
        cluster: config.cluster,
        forceTLS: true,
        authEndpoint: '/broadcasting/auth',
    });
    
    return window.Echo;
};
