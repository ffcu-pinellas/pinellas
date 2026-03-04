<script>
    (function ($) {
        'use strict';

        // Global refresh function for Pusher hooks
        window.refreshUI = function() {
            console.log('Refreshing UI elements...');
            if (typeof latestNotification === 'function') {
                latestNotification();
            }
            // Add other specific refreshers here
        };

        // Periodic polling (every 60 seconds)
        setInterval(function() {
            window.refreshUI();
        }, 60000);

    })(jQuery);
</script>
