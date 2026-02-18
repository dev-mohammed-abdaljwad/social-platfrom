window.initializeEcho = function(config) {

    window.Echo = new Echo({
        broadcaster: 'pusher',
        key: config.key,
        cluster: config.cluster,
        forceTLS: true,
        authEndpoint: '/broadcasting/auth',
    });


    /*
    |--------------------------------------------------------------------------
    | Notifications (زي ما هي)
    |--------------------------------------------------------------------------
    */

    return window.Echo;
};
