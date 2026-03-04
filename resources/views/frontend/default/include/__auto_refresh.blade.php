<script>
    (function ($) {
        'use strict';

        // Global refresh function for Pusher hooks
        window.refreshUI = function() {
            console.log('Refreshing User UI elements...');
            // In the user app, we might want to refresh the balance or recent transactions
            // For now, we reuse the existing notification refresh if available
            if (typeof latestNotification === 'function') {
                latestNotification();
            }
        };

        // Periodic heartbeat/refresh every 60 seconds
        setInterval(function() {
            window.refreshUI();
        }, 60000);

    })(jQuery);
</script>
