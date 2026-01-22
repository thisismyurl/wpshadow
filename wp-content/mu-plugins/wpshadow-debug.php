<?php
declare(strict_types=1);
/*
Plugin Name: WPShadow Debug Config
Description: Suppresses deprecation notices and prevents header issues in dev.
Author: WPShadow
Version: 1.0
*/

if (!defined('ABSPATH')) { exit; }

// Hide errors on screen; log instead
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

// Ensure WordPress debug flags prefer logging without displaying
if (!defined('WP_DEBUG')) { define('WP_DEBUG', true); }
if (!defined('WP_DEBUG_DISPLAY')) { define('WP_DEBUG_DISPLAY', false); }
if (!defined('WP_DEBUG_LOG')) { define('WP_DEBUG_LOG', true); }

// Reduce deprecation noise that breaks headers
$reporting = E_ALL;
if (defined('E_DEPRECATED')) {
    $reporting &= ~E_DEPRECATED;
}
if (defined('E_STRICT')) {
    $reporting &= ~E_STRICT;
}
error_reporting($reporting);
