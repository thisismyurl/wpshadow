<?php
/**
 * Clear PHP opcache
 */

if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "Opcache cleared successfully!";
} else {
    echo "Opcache not available";
}

if (function_exists('opcache_invalidate')) {
    opcache_invalidate('/home/sailmar1/public_html/wpshadow/wp-content/plugins/wpshadow/includes/guardian/class-scan-scheduler.php', true);
    echo "\nInvalidated class-scan-scheduler.php";
}
