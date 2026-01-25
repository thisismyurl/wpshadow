<?php

/**
 * Kanban Report Tool Page
 *
 * @package WPShadow
 */

if (! defined('ABSPATH')) {
	exit;
}

if (! current_user_can('read')) {
	wp_die('Insufficient permissions.');
}

// Include the kanban board view
include WPSHADOW_PATH . 'includes/views/kanban-board.php';
